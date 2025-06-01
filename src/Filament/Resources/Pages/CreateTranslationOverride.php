<?php

namespace JaysonTemporas\TranslationOverrides\Filament\Resources\Pages;

use Filament\Resources\Pages\CreateRecord;
use JaysonTemporas\TranslationOverrides\Filament\Resources\TranslationOverrideResource;

class CreateTranslationOverride extends CreateRecord
{
    protected static string $resource = TranslationOverrideResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Only set tenant ID if tenancy is enabled
        if ($this->isTenancyEnabled()) {
            $tenantIdColumn = config('translation-overrides.tenant_id_column', 'tenant_id');
            $data[$tenantIdColumn] = auth()->user()->getTranslationTenantId();
        }

        return $data;
    }

    protected function afterCreate(): void
    {
        $tableName = config('translation-overrides.table_name', 'translation_overrides');
        
        if ($this->isTenancyEnabled()) {
            $tenantIdColumn = config('translation-overrides.tenant_id_column', 'tenant_id');
            $tenantId = $this->record->$tenantIdColumn;
            cache()->forget("{$tableName}:{$tenantId}");
        } else {
            cache()->forget("{$tableName}:global");
        }
    }

    protected function isTenancyEnabled(): bool
    {
        return config('translation-overrides.tenancy_enabled', true);
    }
}
