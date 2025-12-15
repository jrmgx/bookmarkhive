<?php

namespace App\Service;

use Twig\Attribute\AsTwigFilter;

/**
 * Get the embed code for a given URL.
 */
class FindEmbed
{
    #[AsTwigFilter(name: 'find_embed', isSafe: ['html'])]
    public static function findEmbed(string $url): ?string
    {
        if (preg_match('`youtube\.`i', $url) || preg_match('`youtu\.be`i', $url)) {
            return self::youtubeEmbed($url);
        }

        if (preg_match('`ted\.com/talks/.+`i', $url)) {
            return self::tedEmbed($url);
        }

        if (preg_match('`vimeo\.com/\d+`i', $url)) {
            return self::vimeoEmbed($url);
        }

        if (preg_match('`/w/(\w{22})`', $url, $matches)) {
            // Try to convert it to a new URL with the UUID decoded
            $uuidCondensed = self::convertAnyBase($matches[1], '123456789abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ', '0123456789abcdef');
            preg_match('`^([0-9a-f]{8})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{4})([0-9a-f]{12})$`', $uuidCondensed, $matches);
            if (6 === \count($matches)) {
                unset($matches[0]);
                $string = implode('-', $matches);
                $url = preg_replace('`/w/(\w{22})`', '/videos/watch/' . $string, $url);
            }
        }

        if (!$url) {
            return null;
        }

        // https://peertube.domain/videos/watch/204e3625-3107-429f-a1af-da813023e04a
        if (preg_match('`/videos/watch/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}`', $url)) {
            return self::peerTubeEmbed($url);
        }

        return null;
    }

    private static function peerTubeEmbed(string $url): string
    {
        // https://peertube.nogafa.org/static/previews/204e3625-3107-429f-a1af-da813023e04a.jpg"
        return self::iframe(preg_replace('`/videos/watch/`', '/videos/embed/', $url) ?? '', 'peertube-player');
    }

    private static function vimeoEmbed(string $url): ?string
    {
        // https://vimeo.com/307483271
        $matches = [];
        if (preg_match('`vimeo\.com/(\d+)`i', $url, $matches)) {
            if (!empty($matches[1])) {
                return self::iframe('https://player.vimeo.com/video/' . ((int) $matches[1]), 'vimeo-player');
            }
        }

        return null;
    }

    private static function youtubeEmbed(string $url): ?string
    {
        $id = null;
        if (preg_match('`^https?://(\w+\.)*?youtube\.`i', $url)) {
            // youtube.com
            /** @var array<mixed> $parsedUrl */
            $parsedUrl = parse_url($url);
            $args = [];
            if (empty($parsedUrl['query'] ?? '')) {
                return null;
            }
            parse_str($parsedUrl['query'], $args);
            if (empty($args['v'] ?? '')) {
                return null;
            }

            $id = \is_string($args['v']) ? $args['v'] : '';
        } elseif (preg_match('`^https?://youtu\.be`i', $url)) {
            // youtu.be
            /** @var array<mixed> $parsedUrl */
            $parsedUrl = parse_url($url);

            $id = trim($parsedUrl['path'] ?? '', '/');
        }

        if (!$id) {
            return null;
        }

        // Thumbnail
        return self::iframeWithPlaceholder(
            'https://i2.ytimg.com/vi/' . $id . '/hqdefault.jpg',
            'https://www.youtube-nocookie.com/embed/' . $id . '?autoplay=1',
            'youtube-player'
        );
    }

    private static function tedEmbed(string $url): string
    {
        return self::iframe(preg_replace('`https?://(www\.)?ted\.com/talks/`i', 'https://embed.ted.com/talks/', $url) ?? '', 'ted-player');
    }

    private static function iframeWithPlaceholder(string $imagePlaceholder, string $src, string $class = ''): string
    {
        $iframe = self::iframe($src, $class);
        $uid = uniqid('iframe_');

        return <<<HTML
            <div class="{$class}" id="{$uid}" style="
                display: block;
                height: 100%;
                cursor: pointer;
                background-size: cover;
                background-position: center;
                background-repeat: no-repeat;
                background-color: #ffffff;
                background-image: url({$imagePlaceholder});"
            ></div>
            <script>
                document.getElementById("{$uid}").onclick = function() {
                    this.parentElement.innerHTML = '{$iframe}';
                }
            </script>
            HTML;
    }

    private static function iframe(string $src, string $class = ''): string
    {
        return '<iframe class="' . $class . '" src="' . $src . '" frameborder="0" allowfullscreen></iframe>';
    }

    /**
     * TODO this has to be reworked, respecified.
     */
    private static function convertAnyBase(string $numberInput, string $fromBaseInput, string $toBaseInput): string
    {
        if ($fromBaseInput === $toBaseInput) {
            return $numberInput;
        }

        $fromBase = str_split($fromBaseInput);
        $toBase = str_split($toBaseInput);
        $number = str_split($numberInput);
        $fromLen = \strlen($fromBaseInput);
        $toLen = \strlen($toBaseInput);
        $numberLen = \strlen($numberInput);
        $returnValue = '';

        if ('0123456789' === $toBaseInput) {
            $returnValue = 0;
            for ($i = 1; $i <= $numberLen; ++$i) {
                /* @phpstan-ignore-next-line */
                $returnValue = bcadd((string) $returnValue, bcmul((string) array_search($number[$i - 1], $fromBase, true), bcpow((string) $fromLen, (string) ($numberLen - $i))));
            }

            return (string) $returnValue;
        }

        if ('0123456789' !== $fromBaseInput) {
            $base10 = self::convertAnyBase($numberInput, $fromBaseInput, '0123456789');
        } else {
            $base10 = $numberInput;
        }

        if ($base10 < \strlen($toBaseInput)) {
            return $toBase[(int) $base10];
        }

        while ('0' !== $base10) {
            /* @phpstan-ignore-next-line */
            $returnValue = $toBase[bcmod($base10, (string) $toLen)] . $returnValue;
            $base10 = bcdiv($base10, (string) $toLen);
        }

        return $returnValue;
    }
}
