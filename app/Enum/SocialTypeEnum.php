<?php

declare(strict_types=1);

namespace App\Enum;

enum SocialTypeEnum: string
{
    case YOUTUBE = 'youtube';
    case INSTAGRAM = 'instagram';
    case TELEGRAM = 'telegram';
    case VK = 'vk';
    case SITE = 'site';

    public static function getFromUrl(string $url): ?static
    {
        $url = static::normalizeUrl($url);

        if ($url === null || !filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $host = parse_url($url, PHP_URL_HOST);

        if (!$host) {
            return null;
        }

        $host = strtolower($host);

        $host = static::validateHost($host);

        if ($host === null) {
            return null;
        }

        return match (true) {
            static::isYoutube($host) => static::YOUTUBE,
            static::isInstagram($host) => static::INSTAGRAM,
            static::isTelegram($host) => static::TELEGRAM,
            static::isVk($host) => static::VK,
            default => static::SITE,
        };
    }

    /**
     * @param array<string> $allowedDomains
     */
    public static function domainMatches(string $host, array $allowedDomains): bool
    {
        foreach ($allowedDomains as $domain) {
            if ($host === $domain || str_ends_with($host, '.' . $domain)) {
                return true;
            }
        }

        return false;
    }

    public static function isYoutube(string $host): bool
    {
        return static::domainMatches($host, [
            'youtube.com',
            'youtu.be',
            'youtube-nocookie.com',
        ]);
    }

    public static function isInstagram(string $host): bool
    {
        return static::domainMatches($host, [
            'instagram.com',
        ]);
    }

    public static function isTelegram(string $host): bool
    {
        return static::domainMatches($host, [
            't.me',
            'telegram.me',
            'telegram.org',
        ]);
    }

    public static function isVk(string $host): bool
    {
        return static::domainMatches($host, [
            'vk.com',
            'vk.ru',
            'vkontakte.ru',
        ]);
    }

    private static function normalizeUrl(string $url): ?string
    {
        $url = trim($url);

        if ($url === '') {
            return null;
        }

        if (str_starts_with($url, '//')) {
            $url = 'https:' . $url;
        }

        if (!preg_match('~^[a-z][a-z0-9+\-.]*://~i', $url)) {
            $url = 'https://' . $url;
        }

        $parts = parse_url($url);

        if (!$parts || empty($parts['scheme'])) {
            return null;
        }

        $scheme = strtolower($parts['scheme']);

        if (!in_array($scheme, ['http', 'https'], true)) {
            return null;
        }

        return $url;
    }

    private static function validateHost(string $host): ?string
    {
        $host = strtolower($host);

        // IDN → ASCII
        if (function_exists('idn_to_ascii')) {
            $ascii = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

            if ($ascii === false) {
                return null;
            }

            $host = $ascii;
        }

        if ($host === 'localhost') {
            return null;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return null;
        }

        if (
            filter_var($host, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false &&
            filter_var($host, FILTER_VALIDATE_IP)
        ) {
            return null;
        }

        return $host;
    }
}
