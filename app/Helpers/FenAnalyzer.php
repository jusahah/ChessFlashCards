<?php

namespace App\Helpers;

class FenAnalyzer
{

    public static function whoIsToMove(string $fen): string
    {
        // 'w' or 'b'
        return explode(' ', $fen)[1];
    }
}
