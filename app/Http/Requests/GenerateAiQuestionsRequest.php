<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GenerateAiQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'topik' => ['required', 'string', 'max:1000'],
            'jumlah_pg' => ['required', 'integer', 'min:0', 'max:50'],
            'jumlah_esai' => ['required', 'integer', 'min:0', 'max:20'],
            'kelas' => ['required', 'string', 'max:100'],
            'tingkat_kesulitan' => ['required', 'in:mudah,sedang,sulit,campuran'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $total = (int) $this->input('jumlah_pg') + (int) $this->input('jumlah_esai');

                if ($total < 1) {
                    $validator->errors()->add('jumlah_pg', 'Jumlah soal PG dan esai minimal 1 soal.');
                }

                if ($total > 50) {
                    $validator->errors()->add('jumlah_pg', 'Total soal dalam satu kali generate maksimal 50 soal.');
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'topik.required' => 'Topik materi wajib diisi.',
            'kelas.required' => 'Kelas wajib diisi.',
            'tingkat_kesulitan.required' => 'Tingkat kesulitan wajib dipilih.',
            'tingkat_kesulitan.in' => 'Tingkat kesulitan tidak valid.',
            'jumlah_pg.required' => 'Jumlah soal PG wajib diisi.',
            'jumlah_pg.integer' => 'Jumlah soal PG harus berupa angka bulat.',
            'jumlah_pg.min' => 'Jumlah soal PG minimal 0.',
            'jumlah_pg.max' => 'Jumlah soal PG maksimal 50.',
            'jumlah_esai.required' => 'Jumlah soal esai wajib diisi.',
            'jumlah_esai.integer' => 'Jumlah soal esai harus berupa angka bulat.',
            'jumlah_esai.min' => 'Jumlah soal esai minimal 0.',
            'jumlah_esai.max' => 'Jumlah soal esai maksimal 20.',
        ];
    }
}
