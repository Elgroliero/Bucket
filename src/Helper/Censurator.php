<?php

namespace App\Helper;

use phpDocumentor\Reflection\Types\Self_;

class Censurator
{
    const MOT_CENSURE = ['batard', 'connard', 'fripon'];

    public function __construct()
    {

    }

    //fonction pour censurer un mot
    public function purify(string $text): string
    {
        $file = '../data/forbidden_words.txt';

        $forbiddenWords = file($file);

        foreach ($forbiddenWords as $censure) {
            $censure = str_ireplace(PHP_EOL, '', $censure);
            $remplacement = str_repeat('*', strlen($censure));
            $text = str_ireplace($censure, $remplacement, $text);
        }
        return $text;
    }
}