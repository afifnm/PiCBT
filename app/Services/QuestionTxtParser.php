<?php

namespace App\Services;

class QuestionTxtParser
{
    /** Label opsi yang didukung, sesuai pola di halaman soal. */
    private const LABELS = ['A', 'B', 'C', 'D', 'E'];

    /**
     * Parse teks soal menjadi array terstruktur.
     *
     * @return array{questions: array<int, array<string, mixed>>, errors: array<int, string>}
     */
    public function parse(string $raw): array
    {
        $questions = [];
        $errors    = [];

        $blocks = $this->splitBlocks($raw);

        foreach ($blocks as $i => $block) {
            $no = $i + 1;
            try {
                $questions[] = $this->parseBlock($block);
            } catch (\InvalidArgumentException $e) {
                $errors[] = "Soal #{$no}: {$e->getMessage()}";
            }
        }

        return ['questions' => $questions, 'errors' => $errors];
    }

    /**
     * Pisah teks menjadi blok per soal: dipisah oleh baris kosong atau garis `---`.
     *
     * @return array<int, string>
     */
    private function splitBlocks(string $raw): array
    {
        // Normalkan line ending, abaikan baris komentar yang diawali '#'.
        $raw = str_replace(["\r\n", "\r"], "\n", $raw);

        $lines = [];
        foreach (explode("\n", $raw) as $line) {
            if (str_starts_with(ltrim($line), '#')) {
                continue;
            }
            $lines[] = $line;
        }
        $raw = implode("\n", $lines);

        // Pemisah blok: satu/lebih baris kosong, atau baris berisi hanya '---'.
        $parts = preg_split('/\n\s*(?:---+\s*)?\n+/', $raw) ?: [];

        return array_values(array_filter(array_map('trim', $parts), fn ($b) => $b !== ''));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws \InvalidArgumentException
     */
    private function parseBlock(string $block): array
    {
        $lines = explode("\n", $block);

        $tipe       = 'pilihan_ganda';
        $bobot      = 10.0;
        $pertanyaan = [];
        $rubrik     = null;
        $options    = [];

        // Status untuk menampung lanjutan multi-baris pada field "SOAL:".
        $collectingSoal = false;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '') {
                continue;
            }

            // Field berlabel: KEY: value
            if (preg_match('/^(TIPE|BOBOT|SOAL|RUBRIK|KUNCI)\s*:\s*(.*)$/i', $trimmed, $m)) {
                $key   = strtoupper($m[1]);
                $value = trim($m[2]);
                $collectingSoal = false;

                switch ($key) {
                    case 'TIPE':
                        $tipe = $this->normalizeTipe($value);
                        break;
                    case 'BOBOT':
                        $bobot = is_numeric($value) && (float) $value >= 0.5 ? (float) $value : 10.0;
                        break;
                    case 'SOAL':
                        $pertanyaan[]   = $value;
                        $collectingSoal = true;
                        break;
                    case 'RUBRIK':
                    case 'KUNCI':
                        $rubrik = $value !== '' ? $value : null;
                        break;
                }
                continue;
            }

            // Baris opsi PG: "A. teks", "A) teks", "A: teks", "A teks"
            if (preg_match('/^([A-Ea-e])[\.\)\:\-]?\s+(.*)$/', $trimmed, $m)) {
                $label = strtoupper($m[1]);
                $text  = trim($m[2]);

                $isCorrect = false;
                if (str_ends_with($text, '*')) {
                    $isCorrect = true;
                    $text      = rtrim(substr($text, 0, -1));
                }

                $options[$label] = [
                    'label'      => $label, 
                    'teks_opsi'  => $this->escapeTxt($text), 
                    'is_correct' => $isCorrect
                ];
                $collectingSoal  = false;
                continue;
            }

            // Lanjutan pertanyaan multi-baris.
            if ($collectingSoal) {
                $pertanyaan[] = $trimmed;
            }
        }

        $pertanyaanText = $this->escapeTxt(trim(implode("\n", $pertanyaan)));

        if ($pertanyaanText === '') {
            throw new \InvalidArgumentException('pertanyaan (SOAL:) tidak boleh kosong.');
        }

        if ($tipe === 'pilihan_ganda') {
            return $this->buildPgQuestion($pertanyaanText, $bobot, $options);
        }

        return [
            'tipe'          => 'esai',
            'pertanyaan'    => $pertanyaanText,
            'bobot'         => $bobot,
            'kunci_jawaban' => $rubrik !== null ? $this->escapeTxt($rubrik) : null,
            'options'       => [],
        ];
    }

    /**
     * Mengubah teks menjadi aman dari eksekusi HTML yang tidak disengaja (misal <img>),
     * tetapi tetap mempertahankan tag format dasar (b, i, u, br).
     */
    private function escapeTxt(string $text): string
    {
        $escaped = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        
        $safeTags = ['b', 'i', 'u', 'strong', 'em', 'sub', 'sup', 'br'];
        foreach ($safeTags as $tag) {
            $escaped = preg_replace("/&lt;{$tag}&gt;/i", "<{$tag}>", $escaped);
            $escaped = preg_replace("/&lt;\/{$tag}&gt;/i", "</{$tag}>", $escaped);
        }
        $escaped = preg_replace("/&lt;br\s*\/?&gt;/i", "<br>", $escaped);
        
        // Convert newlines to <br> for plain text rendering in WYSIWYG
        return nl2br($escaped);
    }

    /**
     * @param array<string, array<string, mixed>> $options
     *
     * @return array<string, mixed>
     *
     * @throws \InvalidArgumentException
     */
    private function buildPgQuestion(string $pertanyaan, float $bobot, array $options): array
    {
        if (count($options) < 2) {
            throw new \InvalidArgumentException('soal pilihan ganda butuh minimal 2 opsi.');
        }

        $correct = array_filter($options, fn ($o) => $o['is_correct']);

        if (count($correct) !== 1) {
            throw new \InvalidArgumentException('harus ada tepat 1 kunci jawaban (tandai dengan * di akhir opsi).');
        }

        // Urutkan opsi sesuai label A-E.
        $ordered = [];
        foreach (self::LABELS as $label) {
            if (isset($options[$label])) {
                $ordered[] = $options[$label];
            }
        }

        $kunci = array_key_first($correct);

        return [
            'tipe'          => 'pilihan_ganda',
            'pertanyaan'    => $pertanyaan,
            'bobot'         => $bobot,
            'kunci_jawaban' => $kunci,
            'options'       => $ordered,
        ];
    }

    private function normalizeTipe(string $value): string
    {
        $value = strtolower(trim($value));

        return in_array($value, ['esai', 'essay', 'uraian'], true) ? 'esai' : 'pilihan_ganda';
    }
}
