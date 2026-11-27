<?php

declare(strict_types=1);

namespace App\Support;

class StoreContact
{
    public static function whatsappUrl(?string $message = null): string
    {
        $digits = preg_replace('/\D+/', '', (string) config('store.whatsapp')) ?? '';

        if ($digits !== '' && ! str_starts_with($digits, '55') && strlen($digits) <= 11) {
            $digits = '55'.$digits;
        }

        $baseUrl = "https://wa.me/{$digits}";

        if ($message !== null && $message !== '') {
            return $baseUrl.'?text='.rawurlencode($message);
        }

        return $baseUrl;
    }
}
