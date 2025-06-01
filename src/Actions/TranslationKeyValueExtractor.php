<?php

declare(strict_types=1);

namespace JaysonTemporas\TranslationOverrides\Actions;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class TranslationKeyValueExtractor
{
    /** @return array<string, string> */
    public function handle(string $locale = 'en'): array
    {
        $langPath = base_path('lang');
        
        // Check if the lang directory exists
        if (!is_dir($langPath)) {
            return [];
        }
        
        // Check if the locale folder exists, fallback to 'en' if not
        if (!is_dir("{$langPath}/{$locale}")) {
            $locale = 'en';
            
            // If even the fallback locale doesn't exist, return empty array
            if (!is_dir("{$langPath}/{$locale}")) {
                return [];
            }
        }
        
        $keys = [];
        
        // Process main app translations
        $files = File::allFiles("{$langPath}/{$locale}");
        foreach ($files as $file) {
            $filename = pathinfo((string) $file, PATHINFO_FILENAME);
            $translations = require $file;

            $keys = array_merge(
                $keys,
                $this->extractKeys($translations, $filename)
            );
        }
        
        // Process vendor translations
        $vendorPath = "{$langPath}/vendor";
        if (is_dir($vendorPath)) {
            $packages = File::directories($vendorPath);
            
            foreach ($packages as $package) {
                $packageName = basename($package);
                $localeDir = "{$package}/{$locale}";
                
                // Check if the locale exists for this package, if not try fallback
                if (!is_dir($localeDir)) {
                    $localeDir = "{$package}/en";
                    if (!is_dir($localeDir)) {
                        continue;
                    }
                }
                
                $vendorFiles = File::allFiles($localeDir);
                foreach ($vendorFiles as $file) {
                    $filename = pathinfo((string) $file, PATHINFO_FILENAME);
                    $translations = require $file;
                    
                    // Use the package name with double colons as the top-level prefix for vendor translations
                    $keys = array_merge(
                        $keys,
                        $this->extractKeys($translations, "{$packageName}::{$filename}")
                    );
                }
            }
        }

        return $keys;
    }

    /**
     * @param  array<string, string|array<string, string>>  $translations
     * @return array<string, string>
     */
    protected function extractKeys(array $translations, string $prefix = ''): array
    {
        $keys = [];

        foreach ($translations as $key => $value) {
            // Handle special case for vendor prefixes
            if (strpos($prefix, '::') !== false) {
                $currentKey = $prefix !== '' ? "{$prefix}.{$key}" : $key;
            } else {
                $currentKey = $prefix !== '' && $prefix !== '0' ? "{$prefix}.{$key}" : $key;
            }

            if (is_array($value)) {
                $keys = array_merge(
                    $keys,
                    $this->extractKeys($value, $currentKey)
                );
            } else {
                $keys[$currentKey] = $this->makeHumanReadable($currentKey);
            }
        }

        return $keys;
    }

    protected function makeHumanReadable(string $key): string
    {
        // Handle vendor namespace format with double colons
        if (strpos($key, '::') !== false) {
            $parts = explode('::', $key, 2);
            $vendor = $parts[0];
            $remainingParts = explode('.', $parts[1]);
            
            // Get the last part of the key (most specific part)
            $lastPart = end($remainingParts);
            
            // Convert to title case and replace underscores/dashes with spaces
            $readable = Str::title(str_replace(['_', '-'], ' ', $lastPart));
            
            // Add context with vendor name and path
            $context = Str::title($vendor);
            
            for ($i = 0; $i < count($remainingParts) - 1; $i++) {
                $context .= ' - ' . Str::title(str_replace(['_', '-'], ' ', $remainingParts[$i]));
            }
            
            return "{$context} - {$readable}";
        }

        // Original code for non-vendor keys
        // Split the key into parts
        $parts = explode('.', $key);

        // Get the last part of the key (most specific part)
        $lastPart = end($parts);

        // Convert to title case and replace underscores/dashes with spaces
        $readable = Str::title(str_replace(['_', '-'], ' ', $lastPart));

        // If there are multiple parts, add the context
        if (count($parts) > 1) {
            $context = '';
            for ($i = 0; $i < count($parts) - 1; $i++) {
                if ($i > 0) {
                    $context .= ' - ';
                }

                $context .= Str::title(str_replace(['_', '-'], ' ', $parts[$i]));
            }

            $readable = "{$context} - {$readable}";
        }

        return $readable;
    }
}
