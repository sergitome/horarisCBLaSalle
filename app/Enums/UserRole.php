<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'ADMIN';
    case GESTOR = 'GESTOR';
    case CONSULTA = 'CONSULTA';

    public function canWrite(): bool
    {
        return in_array($this, [self::ADMIN, self::GESTOR], true);
    }

    public function isAdmin(): bool
    {
        return $this === self::ADMIN;
    }
}
