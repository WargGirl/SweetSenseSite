<?php

namespace App\Services;

class HtmlConverter
{
    public static function textToHtml(?string $text): string
    {
        if ($text === null) {
            return '';
        }

        $html = htmlspecialchars((string)$text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $html = preg_replace('/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $html);
        $html = preg_replace('/\*(.*?)\*/s', '<em>$1</em>', $html);
        $html = preg_replace("/\r\n|\r|\n/", '<br>', $html);

        return $html;
    }

    public static function htmlToText(string $html): string
    {
        $text = preg_replace('/<(script|style)[^>]*?>.*?<\/\1>/si', '', $html);
        $text = preg_replace('/<\s*(br\s*\/?>|\/p\s*>|\/h1\s*>|\/h2\s*>|\/li\s*>)/i', "\n", $text);
        $text = preg_replace('/<[^>]+>/', '', $text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/\n[ \t]+/", "\n", $text);
        $text = preg_replace("/\n{3,}/", "\n\n", $text);

        return trim($text);
    }

    public static function saveHtmlAsTextFile(string $html, string $filePath): bool
    {
        return file_put_contents($filePath, self::htmlToText($html)) !== false;
    }
}