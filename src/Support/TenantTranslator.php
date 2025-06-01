<?php

namespace JaysonTemporas\TranslationOverrides\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Translation\Translator;
use JaysonTemporas\TranslationOverrides\Models\TranslationOverride;

class TenantTranslator extends Translator
{
    protected int|null $tenant_id = null;
    protected ?string $tenantIdColumn;
    
    public function __construct($loader, $locale)
    {
        parent::__construct($loader, $locale);
        $this->tenantIdColumn = config('translation-overrides.tenant_id_column', 'tenant_id');
    }

    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
        // If tenancy is disabled, skip tenant resolution
        if (!$this->isTenancyEnabled()) {
            $TranslationOverrides = $this->getGlobalTranslationOverrides($locale);
            
            if (is_array($TranslationOverrides) && count($TranslationOverrides) && isset($TranslationOverrides[$key])) {
                $value = $TranslationOverrides[$key];
                return $this->makeReplacements($value, $replace);
            }
            
            // Fall back to default translation
            return parent::get($key, $replace, $locale, $fallback);
        }

        // Handle tenancy mode
        $user = auth()->user();

        if (auth()->check() && method_exists($user, 'getTranslationTenantId')) {
            $this->tenant_id = $user->getTranslationTenantId();
        } else {
            return parent::get($key, $replace, $locale, $fallback);
        }

        if ($this->tenant_id === 0) {
            return parent::get($key, $replace, $locale, $fallback);
        }

        $TranslationOverrides = $this->getTenantTranslationOverrides($locale);
        
        if (is_array($TranslationOverrides) && count($TranslationOverrides) && isset($TranslationOverrides[$key])) {
            $value = $TranslationOverrides[$key];

            return $this->makeReplacements($value, $replace);
        }

        // Fall back to default translation
        return parent::get($key, $replace, $locale, $fallback);
    }

    protected function getTenantTranslationOverrides(?string $locale): mixed
    {
        $tableName = config('translation-overrides.table_name', 'translation_overrides');
        $cacheKey = "{$tableName}:{$this->tenant_id}";

        return Cache::remember($cacheKey, now()->addMinutes(config('translation-overrides.cache_duration')), fn () => TranslationOverride::where([
            $this->tenantIdColumn => $this->tenant_id,
            'locale' => $locale ?? $this->locale,
        ])->get()->pluck('value', 'key')->toArray());
    }
    
    protected function getGlobalTranslationOverrides(?string $locale): mixed
    {
        $tableName = config('translation-overrides.table_name', 'translation_overrides');
        $cacheKey = "{$tableName}:global";

        return Cache::remember($cacheKey, now()->addMinutes(config('translation-overrides.cache_duration')), fn () => TranslationOverride::where([
            'locale' => $locale ?? $this->locale,
        ])->get()->pluck('value', 'key')->toArray());
    }
    
    protected function isTenancyEnabled(): bool
    {
        return config('translation-overrides.tenancy_enabled', true);
    }
    
    /**
     * Get all supported languages from the configuration.
     *
     * @return array<string, string>
     */
    public static function getSupportedLanguages(): array
    {
        return config('translation-overrides.supported_languages', ['en' => 'English']);
    }
    
    /**
     * Check if a locale is supported.
     *
     * @param string $locale
     * @return bool
     */
    public static function isLocaleSupported(string $locale): bool
    {
        return array_key_exists($locale, static::getSupportedLanguages());
    }
}
