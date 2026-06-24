<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionBank;
use App\Models\QuestionOption;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class QuestionBankSeeder extends Seeder
{
    public function run(): void
    {
        $admin   = User::where('username', 'admin')->first();
        $subjMtk = Subject::where('kode', 'MTK')->first();
        $subjRpl = Subject::where('kode', 'RPL')->first();

        // ----------------------------------------------------------------
        // Bank Soal 1: Matematika Dasar (PG)
        // ----------------------------------------------------------------
        $bankMtk = QuestionBank::firstOrCreate(
            ['judul' => 'Matematika Dasar Kelas X'],
            [
                'subject_id'  => $subjMtk->id,
                'deskripsi'   => 'Soal pilihan ganda dan esai matematika dasar untuk kelas X.',
                'created_by'  => $admin->id,
            ]
        );

        $pgSoal = [
            [
                'pertanyaan' => 'Hasil dari 2³ × 2² adalah ...',
                'bobot'      => 10,
                'kunci'      => 'C',
                'options'    => [
                    'A' => ['teks' => '8',   'correct' => false],
                    'B' => ['teks' => '16',  'correct' => false],
                    'C' => ['teks' => '32',  'correct' => true],
                    'D' => ['teks' => '64',  'correct' => false],
                    'E' => ['teks' => '128', 'correct' => false],
                ],
            ],
            [
                'pertanyaan' => 'Jika f(x) = 3x + 5, maka f(4) adalah ...',
                'bobot'      => 10,
                'kunci'      => 'B',
                'options'    => [
                    'A' => ['teks' => '12',  'correct' => false],
                    'B' => ['teks' => '17',  'correct' => true],
                    'C' => ['teks' => '20',  'correct' => false],
                    'D' => ['teks' => '21',  'correct' => false],
                    'E' => ['teks' => '24',  'correct' => false],
                ],
            ],
            [
                'pertanyaan' => 'Nilai dari √144 adalah ...',
                'bobot'      => 10,
                'kunci'      => 'B',
                'options'    => [
                    'A' => ['teks' => '11', 'correct' => false],
                    'B' => ['teks' => '12', 'correct' => true],
                    'C' => ['teks' => '13', 'correct' => false],
                    'D' => ['teks' => '14', 'correct' => false],
                    'E' => ['teks' => '15', 'correct' => false],
                ],
            ],
            [
                'pertanyaan' => 'Persamaan garis yang melalui titik (2, 3) dengan gradien 2 adalah ...',
                'bobot'      => 10,
                'kunci'      => 'A',
                'options'    => [
                    'A' => ['teks' => 'y = 2x - 1',  'correct' => true],
                    'B' => ['teks' => 'y = 2x + 1',  'correct' => false],
                    'C' => ['teks' => 'y = 2x + 3',  'correct' => false],
                    'D' => ['teks' => 'y = x - 1',   'correct' => false],
                    'E' => ['teks' => 'y = 3x - 2',  'correct' => false],
                ],
            ],
            [
                'pertanyaan' => 'FPB dari 36 dan 48 adalah ...',
                'bobot'      => 10,
                'kunci'      => 'C',
                'options'    => [
                    'A' => ['teks' => '6',   'correct' => false],
                    'B' => ['teks' => '8',   'correct' => false],
                    'C' => ['teks' => '12',  'correct' => true],
                    'D' => ['teks' => '18',  'correct' => false],
                    'E' => ['teks' => '24',  'correct' => false],
                ],
            ],
        ];

        foreach ($pgSoal as $urutan => $soal) {
            $q = Question::firstOrCreate(
                ['question_bank_id' => $bankMtk->id, 'urutan' => $urutan + 1],
                [
                    'tipe'          => 'pilihan_ganda',
                    'pertanyaan'    => $soal['pertanyaan'],
                    'bobot'         => $soal['bobot'],
                    'kunci_jawaban' => $soal['kunci'],
                ]
            );

            if ($q->wasRecentlyCreated) {
                foreach ($soal['options'] as $label => $opt) {
                    QuestionOption::create([
                        'question_id' => $q->id,
                        'label'       => $label,
                        'teks_opsi'   => $opt['teks'],
                        'is_correct'  => $opt['correct'],
                    ]);
                }
            }
        }

        // Esai soal
        $esaiSoal = [
            [
                'pertanyaan'    => 'Jelaskan perbedaan antara bilangan prima dan bilangan komposit. Berikan masing-masing 3 contoh!',
                'bobot'         => 20,
                'kunci_jawaban' => 'Bilangan prima adalah bilangan asli lebih dari 1 yang hanya habis dibagi 1 dan dirinya sendiri. Contoh: 2, 3, 5. Bilangan komposit adalah bilangan asli lebih dari 1 yang memiliki lebih dari 2 faktor. Contoh: 4, 6, 8.',
                'urutan'        => 6,
            ],
            [
                'pertanyaan'    => 'Seorang pedagang membeli 50 kg beras dengan harga Rp 12.000/kg. Kemudian dijual dengan keuntungan 20%. Berapa harga jual per kg dan total pendapatan pedagang tersebut?',
                'bobot'         => 30,
                'kunci_jawaban' => 'Harga beli total = 50 × 12.000 = 600.000. Keuntungan 20% = 120.000. Total jual = 720.000. Harga jual per kg = 720.000 / 50 = 14.400.',
                'urutan'        => 7,
            ],
        ];

        foreach ($esaiSoal as $soal) {
            Question::firstOrCreate(
                ['question_bank_id' => $bankMtk->id, 'urutan' => $soal['urutan']],
                [
                    'tipe'          => 'esai',
                    'pertanyaan'    => $soal['pertanyaan'],
                    'bobot'         => $soal['bobot'],
                    'kunci_jawaban' => $soal['kunci_jawaban'],
                ]
            );
        }

        // ----------------------------------------------------------------
        // Bank Soal 2: Dasar Pemrograman (RPL)
        // ----------------------------------------------------------------
        $bankRpl = QuestionBank::firstOrCreate(
            ['judul' => 'Dasar Pemrograman Web Kelas XI'],
            [
                'subject_id'  => $subjRpl->id,
                'deskripsi'   => 'Soal pemrograman dasar untuk siswa RPL kelas XI.',
                'created_by'  => $admin->id,
            ]
        );

        $rplSoal = [
            [
                'pertanyaan' => 'Tag HTML yang digunakan untuk membuat paragraf adalah ...',
                'bobot'      => 10,
                'kunci'      => 'B',
                'options'    => [
                    'A' => ['teks' => '&lt;h1&gt;', 'correct' => false],
                    'B' => ['teks' => '&lt;p&gt;',  'correct' => true],
                    'C' => ['teks' => '&lt;div&gt;', 'correct' => false],
                    'D' => ['teks' => '&lt;span&gt;', 'correct' => false],
                    'E' => ['teks' => '&lt;br&gt;',  'correct' => false],
                ],
            ],
            [
                'pertanyaan' => 'Manakah yang merupakan tipe data primitif dalam JavaScript?',
                'bobot'      => 10,
                'kunci'      => 'D',
                'options'    => [
                    'A' => ['teks' => 'Array',   'correct' => false],
                    'B' => ['teks' => 'Object',  'correct' => false],
                    'C' => ['teks' => 'Function', 'correct' => false],
                    'D' => ['teks' => 'String',  'correct' => true],
                    'E' => ['teks' => 'Date',    'correct' => false],
                ],
            ],
            [
                'pertanyaan' => 'CSS property yang digunakan untuk mengubah warna teks adalah ...',
                'bobot'      => 10,
                'kunci'      => 'A',
                'options'    => [
                    'A' => ['teks' => 'color',            'correct' => true],
                    'B' => ['teks' => 'font-color',       'correct' => false],
                    'C' => ['teks' => 'text-color',       'correct' => false],
                    'D' => ['teks' => 'background-color', 'correct' => false],
                    'E' => ['teks' => 'foreground-color', 'correct' => false],
                ],
            ],
        ];

        foreach ($rplSoal as $urutan => $soal) {
            $q = Question::firstOrCreate(
                ['question_bank_id' => $bankRpl->id, 'urutan' => $urutan + 1],
                [
                    'tipe'          => 'pilihan_ganda',
                    'pertanyaan'    => $soal['pertanyaan'],
                    'bobot'         => $soal['bobot'],
                    'kunci_jawaban' => $soal['kunci'],
                ]
            );

            if ($q->wasRecentlyCreated) {
                foreach ($soal['options'] as $label => $opt) {
                    QuestionOption::create([
                        'question_id' => $q->id,
                        'label'       => $label,
                        'teks_opsi'   => $opt['teks'],
                        'is_correct'  => $opt['correct'],
                    ]);
                }
            }
        }

        Question::firstOrCreate(
            ['question_bank_id' => $bankRpl->id, 'urutan' => 4],
            [
                'tipe'          => 'esai',
                'pertanyaan'    => 'Jelaskan perbedaan antara HTTP GET dan POST request. Kapan sebaiknya menggunakan masing-masing metode?',
                'bobot'         => 30,
                'kunci_jawaban' => 'GET digunakan untuk mengambil data, parameter tampil di URL, dapat di-bookmark, cached oleh browser. POST digunakan untuk mengirim data, parameter di body request, lebih aman untuk data sensitif, tidak dapat di-bookmark. GET cocok untuk pencarian/filter; POST cocok untuk form login, upload file, submit data.',
            ]
        );
    }
}
