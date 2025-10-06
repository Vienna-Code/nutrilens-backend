<?php

namespace App\Enum;

enum Rol: string
{
    case NO_VERIFICADO = 'no_verificado';
    case VERIFICADO = 'verificado';
    case ADMIN = 'admin';
}