<?php

namespace App\Enum;

/**
 * Tipo de reporte realizada a un comercio o producto
 * 
 * @SUBMISSION Reporte inicial de un comercio o producto
 * @CONFIRMATION Reporte que confirma su existencia y que sus datos son correctos
 * @REBUTTAL Reporte que desmiente su existencia
 * @VERIFICATION Reporte que verifica un comercio o producto
 * @UNVERIFICATION Reporte que le quita la verificación a un comercio o producto
 * @ISSUE Reporte que señala un problema con la información de un comercio o producto
 */
enum ReportType: string
{
    case SUBMISSION = 'submission';
    case CONFIRMATION = 'confirmation';
    case REBUTTAL = 'rebuttal';
    case VERIFICATION = 'verification';
    case UNVERIFICATION = 'deverification';
    case ISSUE = 'issue';
}