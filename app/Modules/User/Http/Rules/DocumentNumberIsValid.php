<?php

namespace App\Modules\User\Http\Rules;

use App\Modules\Document\Services\ValidateDocumentService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class DocumentNumberIsValid implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!(new ValidateDocumentService())->validate($value)) {
            $fail('The :attribute must be a valid document.');
        }
    }
}
