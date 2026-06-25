<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const ENCRYPTED_KEYS = ['gemini_api_key'];

    public function index(): View
    {
        $settings = [
            'gemini_api_key'   => $this->getMasked('gemini_api_key'),
            'gemini_model'     => Setting::get('gemini_model', 'gemini-2.5-flash'),
            'app_name'         => Setting::get('app_name', 'PiCBT'),
            'sekolah_nama'     => Setting::get('sekolah_nama', ''),
            'sekolah_alamat'   => Setting::get('sekolah_alamat', ''),
        ];

        $models = [
            'gemini-2.5-flash'      => 'Gemini 2.5 Flash (terbaru, cepat)',
            'gemini-2.5-flash-lite' => 'Gemini 2.5 Flash Lite (hemat, ringkas)',
            'gemini-2.5-flash'      => 'Gemini 1.5 Flash (lama)',
        ];

        return view('admin.settings.index', compact('settings', 'models'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'gemini_api_key'  => ['nullable', 'string', 'max:200'],
            'gemini_model'    => ['required', 'string', 'max:100'],
            'app_name'        => ['required', 'string', 'max:100'],
            'sekolah_nama'    => ['nullable', 'string', 'max:255'],
            'sekolah_alamat'  => ['nullable', 'string', 'max:500'],
        ]);

        foreach ($data as $key => $value) {
            if ($key === 'gemini_api_key') {
                // Kosong = tidak update key yang tersimpan
                if (! empty($value)) {
                    Setting::set($key, Crypt::encryptString($value));
                }
                continue;
            }
            Setting::set($key, $value);
        }

        return back()->with('success', 'Pengaturan berhasil disimpan.');
    }

    // Test koneksi Gemini API
    public function testGemini(): JsonResponse
    {
        try {
            $encrypted = Setting::where('key', 'gemini_api_key')->value('value');
            $apiKey    = $encrypted ? Crypt::decryptString($encrypted) : config('gemini.api_key');

            if (! $apiKey) {
                return response()->json(['ok' => false, 'message' => 'API key belum dikonfigurasi.']);
            }

            $model = Setting::get('gemini_model', 'gemini-2.5-flash');
            $url   = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            $res = Http::timeout(10)->post($url, [
                'contents' => [['parts' => [['text' => 'Balas dengan kata "OK" saja.']]]],
                'generationConfig' => ['maxOutputTokens' => 10],
            ]);

            if ($res->successful()) {
                $text = $res->json('candidates.0.content.parts.0.text', '');
                return response()->json(['ok' => true, 'message' => "Koneksi berhasil. Response: \"{$text}\""]);
            }

            return response()->json(['ok' => false, 'message' => "API error {$res->status()}: " . $res->json('error.message', 'Unknown error')]);

        } catch (\Throwable $e) {
            return response()->json(['ok' => false, 'message' => $e->getMessage()]);
        }
    }

    private function getMasked(string $key): string
    {
        $encrypted = Setting::where('key', $key)->value('value');
        if (! $encrypted) return '';
        try {
            $plain = Crypt::decryptString($encrypted);
            // Tampilkan sebagian saja: AIza...xxxx
            return substr($plain, 0, 6) . str_repeat('•', max(0, strlen($plain) - 10)) . substr($plain, -4);
        } catch (\Throwable) {
            return '(error membaca)';
        }
    }
}
