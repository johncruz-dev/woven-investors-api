<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Http\UploadedFile;

class ValidCsvUpload implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $value instanceof UploadedFile) {
            $fail('The :attribute must be a valid file.');

            return;
        }

        $path = $value->getRealPath();

        if ($path === false || ! is_readable($path)) {
            $fail('The :attribute could not be read.');

            return;
        }

        $handle = fopen($path, 'rb');

        if ($handle === false) {
            $fail('The :attribute could not be read.');

            return;
        }

        $sample = fread($handle, 8192);
        fclose($handle);

        if ($sample === false || $sample === '') {
            $fail('The :attribute appears to be empty.');

            return;
        }

        if (str_contains($sample, "\0")) {
            $fail('The :attribute must be a plain-text CSV file.');

            return;
        }

        if (! mb_check_encoding($sample, 'UTF-8')) {
            $fail('The :attribute must be UTF-8 encoded.');

            return;
        }

        $normalized = strtolower(ltrim($sample));

        if (str_starts_with($normalized, '<?php') || str_starts_with($normalized, '#!/')) {
            $fail('The :attribute contains disallowed content.');

            return;
        }

        if (! preg_match('/[,;\t\r\n]/', $sample)) {
            $fail('The :attribute does not appear to be a valid CSV file.');
        }
    }
}
