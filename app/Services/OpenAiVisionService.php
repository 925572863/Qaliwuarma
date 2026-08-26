<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

/**
 * Lee una foto de una Pecosa (planilla de productos, escrita a mano o
 * impresa) usando OpenAI (GPT-4o mini) con capacidad de visión, y devuelve
 * la lista de productos detectados en el mismo formato que espera el
 * importador de Excel (cant, unid, descripcion, marca, presentacion, lote).
 *
 * Reemplaza a GeminiVisionService: Gemini (Google) resultó poco confiable
 * para esta tarea (saturaciones 503 frecuentes, timeouts). OpenAI es más
 * estable leyendo tablas en imágenes.
 */
class OpenAiVisionService
{
    private string $apiKey;
    private string $modelo = 'gpt-4o-mini';

    public function __construct()
    {
        $this->apiKey = (string) config('services.openai.key');
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
            throw new \RuntimeException('La IA de fotos no está configurada (falta OPENAI_API_KEY).');
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

MUY IMPORTANTE con las cantidades ("cant"): lee CADA DÍGITO del número, de
izquierda a derecha, sin saltarte ninguno. Los números de cantidad suelen
tener 1 a 4 dígitos (ej. 2, 37, 279, 1225, 6210). Un error común es leer
solo el primer dígito y omitir el resto (leer "6210" como "6", o "1225"
como "1") — verifica con cuidado que copiaste el número completo, dígito
por dígito, antes de escribirlo en el JSON.

MUY IMPORTANTE: la tabla de productos suele tener entre 15 y 25 filas. Debes
extraer TODAS las filas de la tabla, de la primera a la última, sin saltarte
ninguna — incluso las que tengan códigos de lote largos, con barras "/" o "\",
con espacios, o con varios números juntos (ej. "142 26 \ 148 26" o
"KTFBOLOTE3FP:07.05.2026FV:07.05.2030"). Un lote con formato raro NUNCA es
motivo para omitir esa fila: cópialo tal cual se ve, como texto simple, y
sigue con la siguiente fila. Antes de responder, cuenta cuántas filas tiene
la tabla en la imagen y verifica que tu array tenga exactamente esa cantidad
de elementos.

Responde ÚNICAMENTE con un array JSON, sin texto adicional, sin markdown:
[{"cant":10,"unid":"BOLSA","descripcion":"ARROZ FORTIFICADO","marca":"ESPIGA PIURANA","presentacion":1.0,"lote":null}]
PROMPT;

        $payload = [
            'model' => $this->modelo,
            'messages' => [
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => $prompt],
                        ['type' => 'image_url', 'image_url' => ['url' => "data:{$mime};base64,{$data}"]],
                    ],
                ],
            ],
            'temperature' => 0.1,
            'max_tokens'  => 4000,
        ];

        // OpenAI tambien puede saturarse (429/500/502/503/504) o tardar; se
        // reintenta una vez con timeout corto para no exceder el limite de
        // conexion de Render (un request demasiado largo da 502 Bad Gateway).
        $intentos = 2;
        $response = null;
        $excepcion = null;
        for ($i = 1; $i <= $intentos; $i++) {
            try {
                $response = Http::withToken($this->apiKey)->timeout(25)->post(
                    'https://api.openai.com/v1/chat/completions',
                    $payload
                );
                $excepcion = null;
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                $excepcion = $e;
                $response = null;
            }

            if ($response && $response->successful()) break;

            $status = $response?->status();
            $reintentable = $excepcion !== null || in_array($status, [429, 500, 502, 503, 504]);
            if (!$reintentable || $i === $intentos) break;

            sleep(1);
        }

        if ($excepcion !== null) {
            throw new \RuntimeException('No se pudo conectar con la IA de fotos (tardó demasiado en responder). Intenta de nuevo.');
        }

        if (!$response || !$response->successful()) {
            throw new \RuntimeException('Error al conectar con la IA de fotos: ' . $response?->status() . ' - ' . substr($response?->body() ?? '', 0, 300));
        }

        $texto = (string) $response->json('choices.0.message.content', '');

        preg_match('/\[.*\]/s', $texto, $m);
        if (empty($m[0])) {
            \Illuminate\Support\Facades\Log::warning('OpenAiVision: sin JSON en respuesta. Texto: ' . substr($texto, 0, 1000));
            throw new \RuntimeException('La IA no pudo reconocer productos en la foto. Intenta con una foto más clara.');
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
