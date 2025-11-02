<?php

namespace App\Enum;

/**
 * Visibilidad de un recurso
 * 
 * @PUBLIC Visible para todos
 * @PRIVATE Visible solo para el propietario
 * @DELISTED No visible para usuarios
 */
enum Visibility: string
{
    case PUBLIC = 'public';
    case PRIVATE = 'private';
    case DELISTED = 'delisted';
}