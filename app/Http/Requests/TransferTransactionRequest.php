<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->accounts()->where('account_no', $this->input('account_no'))->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_no' => ['required', 'string', 'exists:accounts,account_no'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'target_account_no' => ['nullable', 'string', 'exists:accounts,account_no'],
        ];
    }
}
