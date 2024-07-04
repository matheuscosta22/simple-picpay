<?php

namespace App\Modules\Transaction\Http\Requests;

use App\Modules\User\Constants\UserType;
use Illuminate\Foundation\Http\FormRequest;

class TransactionRequest extends FormRequest
{

    public function authorize(): bool
    {
        return auth()->user()->type == UserType::CUSTOMER;
    }

    protected function prepareForValidation(): void
    {
        $this->offsetSet('payer_id', auth()->user()->id);
    }

    public function rules(): array
    {
        return [
            'receiver_id' => ['required', 'exists:users,id'],
            'value' => ['integer', 'min:1', 'max:100000000'],
        ];
    }
}
