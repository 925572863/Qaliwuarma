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
                // razonamiento cuentan contra maxOutputTokens. thinkingBudget=0
                // no es un valor válido para este modelo (da 400 INVALID_ARGUMENT,
                // confirmado contra la API real), así que se deja un presupuesto
                // bajo en vez de desactivarlo del todo.
                'thinkingConfig'  => ['thinkingBudget' => 300],
            ],
        ];

        // Google satura el modelo con frecuencia (503 "high demand"); es un
        // error pasajero, no un problema real de la foto. Se reintenta una
        // vez con una espera corta antes de rendirse — OJO: Render corta la
        // conexión si el request tarda demasiado (502 Bad Gateway), así que
        // el timeout y la espera deben mantenerse bajos para que el intento
        // completo (incluyendo el reintento) quede muy por debajo de ese
        // límite.
        $intentos = 2;
        $response = null;
        $excepcion = null;
        for ($i = 1; $i <= $intentos; $i++) {
            try {
                $response = Http::timeout(25)->post(
                    "https://generativelanguage.googleapis.com/v1beta/models/{$this->modelo}:generateContent?key={$this->apiKey}",
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
            $mensaje = $response?->status() === 503
                ? 'La IA de Google está saturada por mucha demanda en este momento. Espera un minuto y vuelve a intentar.'
                : 'Error al conectar con la IA de fotos: ' . $response?->status() . ' - ' . substr($response?->body() ?? '', 0, 300);
            throw new \RuntimeException($mensaje);
        }

        $texto = (string) $response->json('candidates.0.content.parts.0.text', '');

        preg_match('/\[.*\]/s', $texto, $m);
        if (empty($m[0])) {
            $finishReason = $response->json('candidates.0.finishReason', '?');
            \Illuminate\Support\Facades\Log::warning('GeminiVision: sin JSON en respuesta. finishReason=' . $finishReason . ' Texto: ' . substr($texto, 0, 1000));
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
