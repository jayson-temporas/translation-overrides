<?php

namespace JaysonTemporas\TranslationOverrides;

use Filament\Contracts\Plugin;
use Filament\Panel;
use JaysonTemporas\TranslationOverrides\Filament\Resources\TranslationOverrideResource;

class TranslationOverridesPlugin implements Plugin
{
    protected string $resourceClass = TranslationOverrideResource::class;

    public function getId(): string
    {
        return 'jaysontemporas-translation-overrides';
    }

    public function register(Panel $panel): void
    {
        $panel
            ->resources([
                $this->resourceClass,
            ]);
    }

    public function boot(Panel $panel): void
    {
        //
    }

    public function usingResource(string $resourceClass): static
    {
        $this->resourceClass = $resourceClass;

        return $this;
    }

    public static function make(): static
    {
        return new static;
    }
}
