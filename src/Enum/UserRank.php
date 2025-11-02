<?php

namespace App\Enum;

/**
 * Rango de un usuario
 */
enum UserRank: string
{
    case BRONZE = 'bronze';
    case SILVER = 'silver';
    case GOLD = 'gold';
    case PLATINUM = 'platinum';
}