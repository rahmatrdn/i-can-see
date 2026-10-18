<?php

namespace App\Constants;

class UserConst
{
    const SUPERADMIN = 1;

    const PARTNER = 2;

    const DEFAULT_PASSWORD = '$2y$12$2pV4WiD9nLczb381xpk20uGq4NnaVhUocp5aciksw5BhcgxkiKDh2';

    public static function getAccessTypes(): array
    {
        return [
            self::SUPERADMIN => 'Super Admin',
            self::PARTNER => 'Partner',
        ];
    }

    public static function getAppAccessTypes(): array
    {
        return self::getAccessTypes();
    }
}
