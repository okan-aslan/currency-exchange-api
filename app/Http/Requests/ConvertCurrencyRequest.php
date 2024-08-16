<?php

namespace App\Http\Requests;

use App\Rules\ValidCurrency;
use App\Services\ExchangeRateService;
use Illuminate\Foundation\Http\FormRequest;

class ConvertCurrencyRequest extends FormRequest
{
    protected $exchangeRateService;

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
            'base_currency' => ['required', 'string', 'size:3', new ValidCurrency($this->exchangeRateService)],
            'target_currency' => ['required', 'string', 'size:3', new ValidCurrency($this->exchangeRateService)],
            'amount' => ['required', 'numeric', 'min:0.01'],

        ];
    }
}
