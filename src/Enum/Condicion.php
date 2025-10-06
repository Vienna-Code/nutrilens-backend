<?php

namespace App\Enum;

enum Condicion: string
{
    case CELIACO = 'celiaco';
    case DIABETICO = 'diabetico';
    case HIPERTENSO = 'hipertenso';
}