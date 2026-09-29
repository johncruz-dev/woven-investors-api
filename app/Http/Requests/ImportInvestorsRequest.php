<?php

namespace App\Http\Requests;

use App\Rules\ValidCsvUpload;
use Illuminate\Foundation\Http\FormRequest;

class ImportInvestorsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:'.config('security.upload.max_size_kb', 10240),
                new ValidCsvUpload,
            ],
        ];
    }
}
