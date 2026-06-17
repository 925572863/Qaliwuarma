<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class GeminiService
{
    private string $apiKey;
    private string $endpoint = 'https://api.groq.com/openai/v1/chat/completions';

    public function __construct()
    {
        $this->apiKey = config('services.groq.key');
    }

    public function calcularNutricion(array $productos, int $totalAlumnos, string $receta = ''): array
    {
        $lista = collect($productos)->map(fn($p) =>
            "- {$p['descripcion']} (presentación: {$p['presentacion']} kg/unid, cantidad: {$p['cant']} unidades)"
        )->join("\n");

        $recetaTexto = $receta
            ? "La receta o preparación descrita es: {$receta}"
            : "Usa una preparación escolar estándar para cada producto.";

        $prompt = <<<PROMPT
Eres un nutricionista del programa Qali Warma de Perú.
Tengo los siguientes productos para preparar para {$totalAlumnos} alumnos de nivel inicial:

{$lista}

{$recetaTexto}

Para cada producto calcula por ración (una porción para un alumno):
1. Gramos por ración
2. Calorías por ración (kcal)
3. Proteínas (g) por ración
4. Carbohidratos (g) por ración
5. Pasos de preparación (breve)
6. Tiempo estimado de preparación

Responde SOLO en formato JSON sin texto adicional:
[
  {
    "descripcion": "nombre del producto",
    "gramos_racion": 60,
    "calorias_racion": 220,
    "proteinas_racion": 8.5,
    "carbohidratos_racion": 35.0,
    "preparacion": "1. Paso uno. 2. Paso dos.",
    "tiempo_preparacion": "20 minutos"
  }
]
PROMPT;

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
        ])->post($this->endpoint, [
            'model'       => 'llama-3.1-8b-instant',
            'messages'    => [
                ['role' => 'user', 'content' => $prompt],
            ],
            'temperature' => 0.2,
            'max_tokens'  => 2000,
        ]);

        if (!$response->successful()) {
            return ['__error' => $response->status(), '__body' => substr($response->body(), 0, 800)];
        }

        $text = $response->json('choices.0.message.content', '');

        preg_match('/\[.*\]/s', $text, $matches);
        if (empty($matches[0])) {
            return ['__error' => 'no_json', '__text' => substr($text, 0, 800)];
        }

        $decoded = json_decode($matches[0], true);
        return $decoded ?? ['__error' => 'json_decode_failed'];
    }
}
