<?php

namespace App\Modules\User\Http\Requests;

use App\Modules\User\Http\Rules\DocumentNumberIsValid;
use Illuminate\Foundation\Http\FormRequest;

class UsersRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->offsetSet('document_number', numbersOnly($this->input('document_number')));
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'min:5', 'max:255'],
            'email' => ['required', 'email', 'email' => 'unique:users,email'],
            'password' => ['required', 'min:6', 'max:50'],
            'document_number' => ['required', 'unique:documents,number', new DocumentNumberIsValid],
        ];
    }
}
