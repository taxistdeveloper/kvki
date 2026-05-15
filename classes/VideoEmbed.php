<?php

class VideoEmbed
{
    public static function extractYouTubeId(string $url): ?string
    {
        if (preg_match('~(?:youtube\.com/watch\?v=|youtube\.com/embed/|youtu\.be/)([a-zA-Z0-9_-]{6,})~', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    public static function extractRutubeId(string $url): ?string
    {
        if (preg_match('~rutube\.ru/(?:video|play/embed)/([a-zA-Z0-9]+)~', $url, $m)) {
            return $m[1];
        }
        return null;
    }

    public static function embedUrl(string $url): ?string
    {
        $url = trim($url);
        if ($url === '') {
            return null;
        }
        if ($id = self::extractYouTubeId($url)) {
            return 'https://www.youtube.com/embed/' . $id . '?rel=0';
        }
        if ($id = self::extractRutubeId($url)) {
            return 'https://rutube.ru/play/embed/' . $id;
        }
        if (preg_match('~vk\.com/video(-?\d+)_(\d+)~', $url, $m)) {
            return 'https://vk.com/video_ext.php?oid=' . $m[1] . '&id=' . $m[2] . '&hd=2';
        }
        if (str_contains($url, '/embed/') || str_contains($url, 'video_ext.php')) {
            return $url;
        }
        return null;
    }

    public static function thumbnailUrl(string $videoUrl, ?string $custom = null): string
    {
        if ($custom) {
            return $custom;
        }
        if ($id = self::extractYouTubeId($videoUrl)) {
            return 'https://img.youtube.com/vi/' . $id . '/hqdefault.jpg';
        }
        return '';
    }
}
