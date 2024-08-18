<?php

namespace App\Http\Requests;

use App\Rules\ValidCurrency;
use App\Services\ExchangeRateService;
use Illuminate\Foundation\Http\FormRequest;

class GetAccountValueRequest extends FormRequest
{
    protected $exchangeRateService;

    /**
     * GetAccountValueRequest constructor.
     *
     * @param ExchangeRateService $exchangeRateService
     */
    public function __construct(ExchangeRateService $exchangeRateService)
    {
        $this->exchangeRateService = $exchangeRateService;
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
            'target_currency' => ['required', 'string', 'max:3', new ValidCurrency($this->exchangeRateService)]
        ];
    }
}
