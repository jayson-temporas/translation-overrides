<?php

declare(strict_types=1);

namespace JaysonTemporas\TranslationOverrides;

use JaysonTemporas\TranslationOverrides\Support\TenantTranslator;
use Illuminate\Foundation\Application;
use Illuminate\Translation\TranslationServiceProvider as BaseTranslationServiceProvider;

class TranslationServiceProvider extends BaseTranslationServiceProvider
{
    public function register(): void
    {
        $this->registerLoader();

        $this->app->singleton('translator', function (Application $app): TenantTranslator {
            $loader = $app['translation.loader'];

            $locale = $app->getLocale();
            
            $translator = new TenantTranslator($loader, $locale);
            $translator->setFallback($app->getFallbackLocale());

            return $translator;
        });
    }
}
