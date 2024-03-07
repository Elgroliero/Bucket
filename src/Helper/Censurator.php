<?php

namespace App\Helper;

class Censurator
{
    private $motcensure = ['batard', 'connard', 'fripon'];

    public function __construct()
    {

    }

    public function purify(string $text): string
    {
        foreach ($this->motcensure as $censure) {
            $text = str_ireplace($censure, '*', $text);
        }
        return $text;
    }
}