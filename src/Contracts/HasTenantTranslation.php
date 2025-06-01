<?php

declare(strict_types=1);

namespace JaysonTemporas\TranslationOverrides\Contracts;

/**
 * Contract for multi-tenant translation support.
 * 
 * Only implement this interface if you have tenancy_enabled set to true
 * in your translation-overrides configuration.
 */
interface HasTenantTranslation
{
    /**
     * Get the tenant ID for translation overrides.
     * 
     * @return int|null The tenant ID
     */
    public function getTranslationTenantId(): int|null;
}