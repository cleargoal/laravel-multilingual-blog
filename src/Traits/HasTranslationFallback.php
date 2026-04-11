<?php

declare(strict_types=1);

namespace YourVendor\Blog\Traits;

/**
 * Trait for models using Spatie Translatable with graceful fallback.
 *
 * Provides getTranslationSafe() method that returns:
 * 1. Translation for requested locale
 * 2. Default locale translation if requested missing
 * 3. Empty string if no translations exist
 */
trait HasTranslationFallback
{
    /**
     * Get translation with fallback chain: requested locale → English → any available.
     * Filters out empty strings (not just null).
     *
     * @param  string  $field  The translatable field name
     * @param  string|null  $locale  Target locale (defaults to app locale)
     * @return string Always returns a string (empty string if no translation found)
     */
    public function getTranslationSafe(string $field, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        // Try requested locale
        $translation = $this->getTranslation($field, $locale);
        if (! empty($translation)) {
            return $translation;
        }

        // Fallback to configured default locale (or English)
        $defaultLocale = config('blog.default_locale', 'en');
        if ($locale !== $defaultLocale) {
            $translation = $this->getTranslation($field, $defaultLocale);
            if (! empty($translation)) {
                return $translation;
            }
        }

        // Last resort: any non-empty translation
        $allTranslations = $this->getTranslations($field);
        foreach ($allTranslations as $trans) {
            if (! empty($trans)) {
                return $trans;
            }
        }

        return '';
    }
}
