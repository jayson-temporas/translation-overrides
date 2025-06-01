<?php

namespace JaysonTemporas\TranslationOverrides\Filament\Resources\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use JaysonTemporas\TranslationOverrides\Filament\Resources\TranslationOverrideResource;

class ListTranslationOverrides extends ListRecords
{
    protected static string $resource = TranslationOverrideResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
