<?php

namespace App\Modules\User\Http\Requests;

use App\Modules\User\Http\Rules\DocumentNumberIsValid;
use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'min:6', 'max:50'],
        ];
    }
}
