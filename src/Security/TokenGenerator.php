<?php


namespace App\Security;


class TokenGenerator
{

    private const KARAKTERLER = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';

    public function getRandomToken(int $length = 30): string
    {
        $token = '';
        $max = strlen(self::KARAKTERLER);

        for($i=0; $i<=$length; $i++){
            $token .= self::KARAKTERLER[random_int(0, $max-1)];
        }

        return $token;
    }

}