<?php

namespace App\Services;

class BookingPhone
{
    public static function normalize(string $phone): ?string
    {
        $number = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($number, '00')) {
            $number = substr($number, 2);
        }
        if (str_starts_with($number, '0')) {
            $number = '62'.substr($number, 1);
        }

        return preg_match('/^[1-9][0-9]{8,14}$/', $number) ? $number : null;
    }

    public static function hash(string $phone): ?string
    {
        $number = self::normalize($phone);

        return $number ? hash_hmac('sha256', 'booking-phone|'.$number, config('app.key')) : null;
    }
}
