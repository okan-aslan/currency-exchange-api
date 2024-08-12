<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class ExchangeRateService
{
    protected $baseUrl = "https://v6.exchangerate-api.com/v6/";
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.exchange_rate.api_key');
    }


    public function getRates($baseCurrency = "USD")
    {
        $url = $this->baseUrl . $this->apiKey . '/latest' . $baseCurrency;

        $response = Http::get($url);

        if ($response->successful()) {
            return $response->json();
        };

        return null;
    }

    public function convertCurrency($baseCurrency, $targetCurrency, $amount)
    {
        $url = $this->baseUrl . $this->apiKey . '/pair/' . $baseCurrency . '/' . $targetCurrency;

        $response = Http::get($url);


        if ($response->successful()) {
            $rate = $response->json()['conversion_rate'];
            return $amount * $rate;
        }

        return null;
    }
}
