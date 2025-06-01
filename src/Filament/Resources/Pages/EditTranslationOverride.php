<?php

namespace JaysonTemporas\TranslationOverrides\Filament\Resources\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use JaysonTemporas\TranslationOverrides\Filament\Resources\TranslationOverrideResource;
use JaysonTemporas\TranslationOverrides\Models\TranslationOverride;

class EditTranslationOverride extends EditRecord
{
    protected static string $resource = TranslationOverrideResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->before(function (TranslationOverride $record): void {
                    $tableName = config('translation-overrides.table_name', 'translation_overrides');
                    
                    if ($this->isTenancyEnabled()) {
                        $tenantIdColumn = config('translation-overrides.tenant_id_column', 'tenant_id');
                        $tenantId = $record->$tenantIdColumn;
                        cache()->forget("{$tableName}:{$tenantId}");
                    } else {
                        cache()->forget("{$tableName}:global");
                    }
                }),
        ];
    }

    public function getRelationManagers(): array
    {
        return [];
    }

    protected function afterSave(): void
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
