<?php

namespace App\Constants;

class DatabaseConst
{
    const SQL_READ = 'mysql_read';

    public static function USER(): string
    {
        $db = self::DB_CORE();

        return $db ? $db.'.users' : 'users';
    }

    public static function SIDEBAR_MENU(): string
    {
        $db = self::DB_CORE();

        return $db ? $db.'.sidebar_menus' : 'sidebar_menus';
    }

    public static function SIDEBAR_MENU_ACCESS(): string
    {
        $db = self::DB_CORE();

        return $db ? $db.'.sidebar_menu_accesses' : 'sidebar_menu_accesses';
    }

    public static function SIDEBAR_MENU_GROUP(): string
    {
        $db = self::DB_CORE();

        return $db ? $db.'.sidebar_menu_groups' : 'sidebar_menu_groups';
    }

    public static function REGISTRATION_PARTNER(): string
    {
        $db = self::DB_CORE();

        return $db ? $db.'.registration_partners' : 'registration_partners';
    }

    public static function APP_USER(): string
    {
        $db = self::DB_CORE();

        return $db ? $db.'.app_users' : 'app_users';
    }

    public static function DB_CORE(): string
    {
        if (config('database.default') === 'sqlite') {
            return '';
        }

        return config('database.connections.mysql.database', 'default');
    }
}
