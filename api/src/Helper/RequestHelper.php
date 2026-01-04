<?php

namespace App\Helper;

use Symfony\Component\HttpFoundation\Request;

class RequestHelper
{
    public static function accepts(Request $request, string $type): bool
    {
        $accept = $request->headers->get('Accept');
        if (!$accept) {
            // Default to application/json if no Accept header is provided
            return 'application/json' === $type;
        }

        return str_contains($accept, $type);
    }
}
