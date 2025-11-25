<?php

namespace App\Enum;

/**
 * Tipo de Comercio
 */
enum CommerceType: string
{
    case SUPERMARKET = 'supermarket';
    case KIOSK = 'kiosk';
    case RESTAURANT = 'restaurant';
    case BAKERY = 'bakery';
}