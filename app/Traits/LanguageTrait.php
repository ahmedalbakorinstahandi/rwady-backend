<?php

namespace App\Traits;

trait LanguageTrait
{
    public function getAllTranslations(string $field): array
    {
        $locales = config('translatable.locales');
        $translations = $this->getTranslations($field);
        $result = [];

        foreach ($locales as $locale) {
            $result[$locale] = $translations[$locale] ?? null;
        }

        return $result;
    }
}
