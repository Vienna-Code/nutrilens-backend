<?php

namespace App\Enum;

/**
 * Métodos de pago
 */
enum PaymentMethod: string
{
    case EFECTIVO = 'efectivo';
    case CREDIT = 'credito';
    case DEBIT = 'debito';
}