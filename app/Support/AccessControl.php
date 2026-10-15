<?php

namespace App\Support;

class AccessControl
{
    /** @var array<string, list<string>> */
    public const ROLE_PERMISSIONS = [
        'Admin' => ['manage skills', 'manage job matches', 'manage user roles'],
        'Manager' => ['manage skills'],
        'User' => [],
    ];
}
