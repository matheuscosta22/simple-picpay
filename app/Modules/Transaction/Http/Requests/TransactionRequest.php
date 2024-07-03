<?php

namespace App\Modules\Transaction\Http\Requests;

use App\Modules\User\Constants\UserType;
use App\Modules\User\Http\Rules\DocumentNumberIsValid;
use App\Modules\User\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{

    public function authorize(): bool
    {
        return $this->input('payer_id') == auth()->user()->id &&
            auth()->user()->type == UserType::CUSTOMER;
    }

    public function rules(): array
    {
        return [
            'payer_id' => ['required', 'exists:users,id'],
            'receiver_id' => ['required', 'exists:users,id'],
            'value' => ['integer', 'min:1'],
        ];
    }
}
