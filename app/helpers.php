<?php

use Illuminate\Support\Facades\DB;

if (!function_exists('format_rupiah')) {
    function format_rupiah($amount) {
        return 'Rp ' . number_format($amount ?? 0, 0, ',', '.');
    }
}
if (!function_exists('format_number_id')) {
    function format_number_id($number) {
        return number_format($number ?? 0, 0, ',', '.');
    }
}
