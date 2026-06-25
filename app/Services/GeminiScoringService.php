<?php

namespace App\Services;

use App\Models\AttemptAnswer;
use App\Models\Setting;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiScoringService
{
    private string $apiKey;
    private string $model;
    private string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = $this->resolveApiKey();
        $this->model  = Setting::get('gemini_model', 'gemini-2.5-flash');
    }

    /**
     * Score a single essay answer. Returns true on success, false on failure.
     * Caller must wrap in DB::transaction() + lockForUpdate() on the attempt.
     */
    public function scoreAnswer(AttemptAnswer $answer): bool
    {
        $question = $answer->question;

        if ($question->tipe !== 'esai') {
            return false;
        }

        // Skip essays that already have a correction (AI or manual).
        // AI correction must never overwrite an existing score.
        if ($answer->dinilai_oleh !== null) {
            return false;
        }

        $payload = $this->buildPayload(
            pertanyaan:    $question->pertanyaan,
            rubrik:        $question->kunci_jawaban ?? '',
            jawabanSiswa:  $answer->jawaban_esai ?? '',
            bobotMaksimal: (float) $question->bobot,
        );

        try {
            $response = Http::timeout(30)
                ->retry(2, 1000)
                ->post($this->endpointUrl(), $payload);

            if (! $response->successful()) {
                Log::warning('Gemini API non-200', [
                    'status'        => $response->status(),
                    'attempt_answer'=> $answer->id,
                    'body'          => $response->body(),
                ]);

                return $this->markPendingManual($answer);
            }

            $json   = $response->json();
            $result = $this->parseResponse($json, (float) $question->bobot);

            $answer->update([
                'skor'         => $result['skor'],
                'ai_feedback'  => $result['feedback'],
                'dinilai_oleh' => 'ai',
            ]);

            $this->accumulateTokens($json);

            return true;

        } catch (ConnectionException $e) {
            Log::error('Gemini connection error', ['error' => $e->getMessage(), 'answer_id' => $answer->id]);
            return $this->markPendingManual($answer);

        } catch (\Throwable $e) {
            Log::error('Gemini scoring exception', ['error' => $e->getMessage(), 'answer_id' => $answer->id]);
            return $this->markPendingManual($answer);
        }
    }

    /**
     * Batch-score all un-corrected essay answers for an attempt.
     * Answers that already have a correction (dinilai_oleh set) are skipped.
     * Returns ['success' => n, 'failed' => n].
     */
    public function scoreAttempt(int $attemptId): array
    {
        $answers = AttemptAnswer::where('exam_attempt_id', $attemptId)
            ->whereHas('question', fn ($q) => $q->where('tipe', 'esai'))
            ->whereNull('dinilai_oleh')
            ->with('question')
            ->get();

        $success = 0;
        $failed  = 0;

        foreach ($answers as $answer) {
            $this->scoreAnswer($answer) ? $success++ : $failed++;
            // Avoid hammering rate limits on batch runs
            usleep(300_000); // 300ms between calls
        }

        return compact('success', 'failed');
    }

    // -----------------------------------------------------------------------
    // Internal helpers
    // -----------------------------------------------------------------------

    private function buildPayload(
        string $pertanyaan,
        string $rubrik,
        string $jawabanSiswa,
        float  $bobotMaksimal,
    ): array {
        $prompt = <<<PROMPT
Anda adalah penilai ujian SMK yang objektif dan konsisten.

Nilai jawaban siswa berdasarkan rubrik yang diberikan.

Pertanyaan:
{$pertanyaan}

Rubrik / Jawaban Acuan:
{$rubrik}

Jawaban Siswa:
{$jawabanSiswa}

Skor Maksimal: {$bobotMaksimal}

Instruksi:
- Berikan skor antara 0 hingga {$bobotMaksimal} (boleh desimal, maks 1 angka di belakang koma).
- Jika jawaban kosong atau tidak relevan, skor = 0.
- Tulis feedback singkat (maks 100 kata) dalam Bahasa Indonesia.
- Balas HANYA dalam format JSON berikut, tanpa teks lain:
{"skor": <angka>, "feedback": "<teks>"}
PROMPT;

        return [
            'contents' => [
                ['parts' => [['text' => $prompt]]],
            ],
            'generationConfig' => [
                'temperature'       => 0.1,  // Low temp for consistent scoring
                'maxOutputTokens'   => 256,
                'responseMimeType'  => 'application/json',
            ],
        ];
    }

    private function parseResponse(array $json, float $bobotMaksimal): array
    {
        $raw = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if ($raw === null) {
            throw new \RuntimeException('Empty Gemini response');
        }

        // Strip markdown code fences if Gemini adds them despite responseMimeType
        $raw = preg_replace('/^```json\s*|\s*```$/s', '', trim($raw));

        $decoded = json_decode($raw, true);

        if (json_last_error() !== JSON_ERROR_NONE || ! isset($decoded['skor'])) {
            throw new \RuntimeException('Invalid JSON from Gemini: ' . $raw);
        }

        $skor = max(0, min((float) $decoded['skor'], $bobotMaksimal));

        return [
            'skor'     => round($skor, 1),
            'feedback' => mb_substr(trim($decoded['feedback'] ?? ''), 0, 1000),
        ];
    }

    private function markPendingManual(AttemptAnswer $answer): bool
    {
        $answer->update([
            'skor'         => null,
            'ai_feedback'  => 'Penilaian AI gagal — menunggu koreksi manual.',
            'dinilai_oleh' => null,
        ]);

        return false;
    }

    private function accumulateTokens(array $json): void
    {
        $usage = $json['usageMetadata'] ?? [];
        $in    = (int) ($usage['promptTokenCount']     ?? 0);
        $out   = (int) ($usage['candidatesTokenCount'] ?? 0);

        if ($in === 0 && $out === 0) {
            return;
        }

        Setting::set('ai_tokens_input',  (int) Setting::get('ai_tokens_input',  0) + $in);
        Setting::set('ai_tokens_output', (int) Setting::get('ai_tokens_output', 0) + $out);
    }

    private function endpointUrl(): string
    {
        return "{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}";
    }

    private function resolveApiKey(): string
    {
        // Prefer env so dev machines don't need DB
        $envKey = config('services.gemini.api_key');
        if ($envKey) {
            return $envKey;
        }

        $encrypted = Setting::where('key', 'gemini_api_key')->value('value');
        if (! $encrypted) {
            throw new \RuntimeException('Gemini API key not configured. Set GEMINI_API_KEY in .env or via Admin → Settings.');
        }

        return Crypt::decryptString($encrypted);
    }
}
