<?php

declare(strict_types=1);

namespace Cleargoal\Blog\Contracts;

interface BlogTranslationProvider
{
    /**
     * Translate text from one language to another.
     *
     * @param  string  $text  Text to translate
     * @param  string  $from  Source language code (e.g., 'en')
     * @param  string  $to  Target language code (e.g., 'uk')
     * @return string Translated text
     *
     * @throws \RuntimeException if translation fails
     */
    public function translate(string $text, string $from, string $to): string;

    /**
     * Check if translation is supported for the given language pair.
     */
    public function canTranslate(string $from, string $to): bool;

    /**
     * Get the list of supported target languages.
     *
     * @return array<string>
     */
    public function supportedLanguages(): array;

    /**
     * Translate text to all configured languages.
     *
     * @param  string  $text  Text to translate
     * @param  string  $fromLocale  Source language code (e.g., 'en')
     * @return array<string, string>|null Keyed by locale, or null on failure
     */
    public function translateToAll(string $text, string $fromLocale): ?array;
}
