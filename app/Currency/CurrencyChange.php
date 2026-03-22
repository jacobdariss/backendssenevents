<?php

namespace App\Currency;
 use Modules\Currency\Models\Currency;

class CurrencyChange
{
    public $defaultCurrency;

    public $currencyList;

    public function __construct()
    {
        $this->currencyList = Currency::all();
        $this->defaultCurrency = $this->currencyList->where('is_primary', 1)->first();

    }

    public function getDefaultCurrency($array = false)
    {
        if ($array && isset($this->defaultCurrency)) {
            return $this->defaultCurrency->toArray() ?? [];
        }

        return $this->defaultCurrency;
    }

    public function defaultSymbol()
    {
        return $this->defaultCurrency->currency_symbol ?? '';
    }

    public function format($amount)
    {
        $amount = is_numeric($amount) ? (float) $amount : 0;

        if (!$this->defaultCurrency) {
            // Fallback XOF : pas de devise configurée
            return number_format($amount, 0, '.', ' ') . ' XOF';
        }

        $noOfDecimal       = $this->defaultCurrency->no_of_decimal       ?? 0;
        $decimalSeparator  = $this->defaultCurrency->decimal_separator    ?? '';
        $thousandSeparator = $this->defaultCurrency->thousand_separator   ?? ' ';
        $currencyPosition  = $this->defaultCurrency->currency_position    ?? 'right_with_space';
        $currencySymbol    = $this->defaultCurrency->currency_symbol      ?? 'XOF';

        return formatCurrency($amount, $noOfDecimal, $decimalSeparator, $thousandSeparator, $currencyPosition, $currencySymbol);
    }
}
