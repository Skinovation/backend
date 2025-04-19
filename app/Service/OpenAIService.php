<?php

namespace App\Service;

use OpenAI;
use Illuminate\Support\Facades\Log;

class OpenAIService
{
    protected $client;

    public function __construct()
    {
        $this->client = OpenAI::client(env('OPENAI_API_KEY'));
    }

    /**
     * Menerjemahkan kalimat dari Bahasa Inggris ke Bahasa Indonesia
     *
     * @param string $prompt
     * @return string
     */
    public function askGPT(string $prompt): string
    {
        try {
            $response = $this->client->chat()->create([
                'model' => 'gpt-4',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'Kamu adalah sistem penerjemah yang menerjemahkan deskripsi produk dari bahasa Inggris ke bahasa Indonesia secara akurat dan alami.',
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt,
                    ],
                ],
                'temperature' => 0.7,
            ]);

            return $response['choices'][0]['message']['content'] ?? 'Terjemahan tidak tersedia.';
        } catch (\Exception $e) {
            // Optional: log error dan kembalikan string fallback
            Log::error('❌ Gagal menerjemahkan dengan OpenAI: ' . $e->getMessage());
            return 'Terjemahan gagal.';
        }
    }
}
