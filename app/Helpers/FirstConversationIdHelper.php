<?php

namespace App\Helpers;

class FirstConversationIdHelper
{
    public static function get(array $conversations): ?int
    {
        $first = $conversations[0] ?? null;
        $id = data_get($first, 'id');
        return $id ? (int) $id : null;
    }
}
