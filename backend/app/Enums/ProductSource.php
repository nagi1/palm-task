<?php

namespace App\Enums;

enum ProductSource: string
{
    case Amazon = 'amazon';
    case Jumia = 'jumia';
    case Unknown = 'unknown';
}
