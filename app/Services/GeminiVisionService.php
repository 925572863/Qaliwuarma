<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Lee una foto de una Pecosa (planilla de productos, escrita a mano o
 * impresa) usando Gemini (Google) con capacidad de visión, y devuelve la
 * lista de productos detectados en el mismo formato que espera el
 * importador de Excel (cant, unid, descripcion, marca, presentacion, lote).
 */
class GeminiVisionService
{
    private string $apiKey;
    private string $modelo = 'gemini-3.6-flash';

    public function __construct()
    {
        $this->apiKey = (string) config('services.gemini.key');
    }

    public function configurado(): bool
    {
        return $this->apiKey !== '';
    }

    /**
     * @return array<int, array{cant:int, unid:string, descripcion:string, marca:?string, presentacion:float, lote:?string}>
     */
    public function extraerProductosDeImagen(string $rutaImagen): array
    {
        if (!$this->configurado()) {
            throw new \RuntimeException('La IA de fotos no está configurada (falta GEMINI_API_KEY).');
        }

        $mime = mime_content_type($rutaImagen) ?: 'image/jpeg';
        $data = base64_encode((string) file_get_contents($rutaImagen));

        $prompt = <<<PROMPT
Eres un asistente que lee una PECOSA (planilla de entrega de productos del programa Qali Warma de Perú), sea escrita a mano o impresa. Identifica cada producto de la tabla/lista y extrae sus datos.

Para cada producto determina:
- cant: cantidad (número entero)
- unid: unidad de medida (BOLSA, BOTELLA, LATA, POUCH, KG, SACO, CAJA, etc.)
- descripcion: nombre del producto (ej. "ARROZ FORTIFICADO", "ACEITE VEGETAL COMESTIBLE")
- marca: marca del producto si aparece, si no null
- presentacion: peso o volumen de cada unidad en kg o litros (número decimal, ej. 1.000 para una bolsa de 1kg, 0.200 para 200ml)
- lote: código de lote si aparece, si no null

Si no puedes leer un dato con certeza, usa tu mejor estimación razonable, nunca inventes productos que no aparecen.

Responde ÚNICAMENTE con un array JSON, sin texto adicional, sin markdown:
[{"cant":10,"unid":"BOLSA","descripcion":"ARROZ FORTIFICADO","marca":"ESPIGA PIURANA","presentacion":1.0,"lote":null}]
PROMPT;

        $response = Http::timeout(45)->post(
            "https://generativelanguage.googleapis.com/v1beta/models/{$this->modelo}:generateContent?key={$this->apiKey}",
            [
                'contents' => [[
                    'parts' => [
                        ['text' => $prompt],
                        ['inline_data' => ['mime_type' => $mime, 'data' => $data]],
                    ],
                ]],
                'generationConfig' => [
                    'temperature'     => 0.1,
                    'maxOutputTokens' => 8000,
                    // gemini-3.6-flash "piensa" antes de responder y esos tokens de
                    // razonamiento cuentan contra maxOutputTokens; sin desactivarlo
                    // se puede quedar sin espacio para la respuesta final (igual
                    // que pasó con el modelo de Groq antes).
                    'thinkingConfig'  => ['thinkingBudget' => 0],
                ],
            ]
        );

        if (!$response->successful()) {
            throw new \RuntimeException('Error al conectar con la IA de fotos: ' . $response->status() . ' - ' . substr($response->body(), 0, 300));
        }

        $texto = (string) $response->json('candidates.0.content.parts.0.text', '');

        preg_match('/\[.*\]/s', $texto, $m);
        if (empty($m[0])) {
            $finishReason = $response->json('candidates.0.finishReason', '?');
            \Illuminate\Support\Facades\Log::warning('GeminiVision: sin JSON en respuesta. finishReason=' . $finishReason . ' Texto: ' . substr($texto, 0, 1000) . ' | Respuesta cruda: ' . substr($response->body(), 0, 1000));
            throw new \RuntimeException('La IA no pudo reconocer productos en la foto. Intenta con una foto más clara. [debug finishReason=' . $finishReason . ': ' . substr($texto, 0, 300) . ']');
        }

        $decoded = json_decode($m[0], true);
        if (!is_array($decoded)) {
            throw new \RuntimeException('No se pudo interpretar la respuesta de la IA.');
        }

        $productos = [];
        foreach ($decoded as $item) {
            if (empty($item['descripcion'])) continue;
            $productos[] = [
                'cant'         => max(1, (int) ($item['cant'] ?? 1)),
                'unid'         => strtoupper((string) ($item['unid'] ?? 'UNIDAD')),
                'descripcion'  => strtoupper((string) $item['descripcion']),
                'marca'        => !empty($item['marca']) ? strtoupper((string) $item['marca']) : null,
                'presentacion' => (float) ($item['presentacion'] ?? 1),
                'lote'         => !empty($item['lote']) ? (string) $item['lote'] : null,
            ];
        }

        return $productos;
    }
}
