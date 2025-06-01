<?php

declare(strict_types=1);

namespace JaysonTemporas\TranslationOverrides\Filament\Resources\Pages;

use Filament\Resources\Pages\ViewRecord;
use JaysonTemporas\TranslationOverrides\Filament\Resources\TranslationOverrideResource;

class ViewTranslationOverride extends ViewRecord
{
    protected static string $resource = TranslationOverrideResource::class;
}
