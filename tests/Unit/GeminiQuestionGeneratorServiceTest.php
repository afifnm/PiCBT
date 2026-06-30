<?php

namespace Tests\Unit;

use App\Models\QuestionBank;
use App\Models\Subject;
use App\Services\GeminiQuestionGeneratorService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class GeminiQuestionGeneratorServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.gemini.api_key' => 'test-api-key',
            'services.gemini.model' => 'gemini-test',
        ]);
    }

    public function test_it_generates_valid_import_text(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => $this->validOutput()]]],
                ]],
            ]),
        ]);

        $result = app(GeminiQuestionGeneratorService::class)->generate($this->bank(), [
            'topik' => 'Fotosintesis',
            'jumlah_pg' => 1,
            'jumlah_esai' => 1,
            'kelas' => 'VII SMP',
            'tingkat_kesulitan' => 'sedang',
        ]);

        $this->assertSame(1, $result['jumlah_pg']);
        $this->assertSame(1, $result['jumlah_esai']);
        $this->assertSame(2, $result['total']);
        $this->assertSame($this->validOutput(), $result['teks']);

        Http::assertSent(fn ($request) => str_contains($request->url(), 'gemini-test:generateContent')
            && str_contains($request['contents'][0]['parts'][0]['text'], 'Fotosintesis')
            && $request['generationConfig']['responseMimeType'] === 'text/plain'
        );
    }

    public function test_it_rejects_ai_output_with_an_invalid_import_format(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => "TIPE: pg\nBOBOT: 10\nSOAL: Soal tanpa kunci?\nA. Ya\nB. Tidak"]]],
                ]],
            ]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Format hasil AI belum valid');

        app(GeminiQuestionGeneratorService::class)->generate($this->bank(), [
            'topik' => 'Materi',
            'jumlah_pg' => 1,
            'jumlah_esai' => 0,
            'kelas' => 'X',
            'tingkat_kesulitan' => 'mudah',
        ]);
    }

    public function test_it_rejects_pg_without_exactly_options_a_through_d(): void
    {
        Http::fake([
            '*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => "TIPE: pg\nBOBOT: 10\nSOAL: Pilih jawaban.\nA. Satu*\nB. Dua\nC. Tiga\nE. Empat"]]],
                ]],
            ]),
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Format hasil AI belum valid');

        app(GeminiQuestionGeneratorService::class)->generate($this->bank(), [
            'topik' => 'Materi',
            'jumlah_pg' => 1,
            'jumlah_esai' => 0,
            'kelas' => 'X',
            'tingkat_kesulitan' => 'mudah',
        ]);
    }

    private function bank(): QuestionBank
    {
        $bank = new QuestionBank(['judul' => 'Bank IPA']);
        $bank->setRelation('subject', new Subject(['nama' => 'IPA', 'kode' => 'IPA']));

        return $bank;
    }

    private function validOutput(): string
    {
        return <<<'TXT'
TIPE: pg
BOBOT: 10
SOAL: Gas yang dihasilkan dalam fotosintesis adalah ...
A. Nitrogen
B. Oksigen*
C. Karbon dioksida
D. Hidrogen

TIPE: esai
BOBOT: 20
SOAL: Jelaskan proses fotosintesis secara singkat.
RUBRIK: Menyebutkan air, karbon dioksida, cahaya, glukosa, dan oksigen; masing-masing bernilai 4 poin.
TXT;
    }
}
