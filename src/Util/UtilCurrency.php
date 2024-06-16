<?php

namespace TopdataSoftwareGmbH\Util;

/**
 * from smartdonatin
 */
class UtilCurrency
{

    /**
     * @param string $currencyCode
     * @return string
     */
    public static function getCurrencySymbol($currencyCode)
    {
        return [
            'EUR' => '€',
            'GBP' => '£',
            'USD' => '$',
        ][$currencyCode];
    }

}