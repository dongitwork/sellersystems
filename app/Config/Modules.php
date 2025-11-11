<?php
namespace App\Config;

class Modules {
    public static function getActive(): array {
        return [
            'Auth',
            'Dashboard',
            'User'
        ];
    }
}