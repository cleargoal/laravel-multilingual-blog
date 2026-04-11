<?php

declare(strict_types=1);

namespace YourVendor\Blog\Services;

use YourVendor\Blog\Contracts\BlogTranslationProvider;

/**
 * Adapter for DeepL translation service.
 *
 * This adapter can work with:
 * - yourvendor/laravel-deepl-translations package (with key rotation)
 * - Direct DeepL API integration
 * - Any other translation service you implement
 *
 * Configure in config/blog.php:
 * 'translation' => [
 *     'provider' => \YourVendor\Blog\Services\DeepLTranslationAdapter::class,
 *     'enabled' => true,
 * ]
 */
class DeepLTranslationAdapter implements BlogTranslationProvider
{
    protected $manager;

    public function __construct()
    {
        // Check if a translation package/service is available
        // Adjust this to match your actual translation implementation
        if (class_exists('\\YourVendor\\DeepLTranslations\\DeepLApiKeyManager')) {
            $this->manager = app('\\YourVendor\\DeepLTranslations\\DeepLApiKeyManager');
        } elseif (config('services.deepl.api_key')) {
            // Fallback to direct DeepL API usage if configured
            $this->manager = null; // Will use direct API calls
        } else {
            throw new \RuntimeException(
                'No translation service configured. '.
                'Either install a DeepL package or configure services.deepl.api_key in config/services.php'
            );
        }
    }

    public function translate(string $text, string $from, string $to): string
    {
        if ($this->manager) {
            // Use translation package/manager
            return $this->manager->translate($text, $from, $to);
        }

        // Fallback: Direct API implementation would go here
        // For now, throw exception to indicate implementation needed
        throw new \RuntimeException(
            'Direct DeepL API translation not implemented. '.
            'Please install a translation package or implement direct API calls.'
        );
    }

    public function canTranslate(string $from, string $to): bool
    {
        $supported = $this->supportedLanguages();

        return in_array($from, $supported) && in_array($to, $supported);
    }

    public function supportedLanguages(): array
    {
        // DeepL supported languages
        // Extend this based on your DeepL plan and requirements
        return [
            'en', 'uk', 'de', 'fr', 'es', 'it', 'pl', 'pt', 'ru', 'ja', 'zh',
            'nl', 'cs', 'da', 'fi', 'el', 'hu', 'id', 'ko', 'lv', 'lt',
            'nb', 'ro', 'sk', 'sl', 'sv', 'tr', 'bg', 'et',
        ];
    }
}
