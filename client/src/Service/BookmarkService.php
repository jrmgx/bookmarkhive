<?php

namespace App\Service;

use Twig\Attribute\AsTwigFilter;

class BookmarkService
{
    #[AsTwigFilter('host')]
    public function host(string $url): string
    {
        $host = parse_url($url, \PHP_URL_HOST);

        if (!$host) {
            return '';
        }

        return $host;
    }
}
