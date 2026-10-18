<?php

namespace App\Constants;

class UserConst
{
    const SUPERADMIN = 1;

    const PARTNER = 2;

    const APP_USER = 3;

    const DEFAULT_PASSWORD = '$2y$12$2pV4WiD9nLczb381xpk20uGq4NnaVhUocp5aciksw5BhcgxkiKDh2';

    public static function getAccessTypes(): array
    {
        return [
            self::SUPERADMIN => 'Super Admin',
            self::PARTNER => 'Partner',
            self::APP_USER => 'App User',
        ];
    }

    public static function getAppAccessTypes(): array
    {
        return [
            self::SUPERADMIN => 'Super Admin',
            self::PARTNER => 'Partner',
        ];
    }
}
