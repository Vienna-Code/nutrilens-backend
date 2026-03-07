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
    case ICE_CREAM_PARLOR = 'ice cream parlor';
    case WAREHOUSE = 'warehouse';
    case BAR = 'bar';
    case ENTREPRENEURSHIP = 'Entrepreneurship';
}