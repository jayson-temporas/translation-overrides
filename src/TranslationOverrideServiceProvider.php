<?php

namespace JaysonTemporas\TranslationOverrides;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class TranslationOverrideServiceProvider extends PackageServiceProvider
{
    public static string $name = 'translation-overrides';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasMigration('create_translation_overrides_table')
            // Publishing groups
            ->hasInstallCommand(function ($command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations();
            });
    }
}
