<?php

if (! function_exists('format_rupiah')) {
    /**
     * Format number to Rupiah currency format.
     *
     * @param  float|int  $amount
     */
    function format_rupiah($amount, bool $prefix = true): string
    {
        return ($prefix ? 'Rp ' : '').number_format($amount, 0, ',', '.');
    }
}
