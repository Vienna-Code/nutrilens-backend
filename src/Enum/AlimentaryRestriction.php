<?php

namespace App\Enum;

/**
 * Restricción alimentaria
 */
enum AlimentaryRestriction: string
{
    case CELIAC = 'celiac';
    case DIABETIC = 'diabetic';
    case HYPERTENSION = 'hypertense';
}