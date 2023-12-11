<?php

namespace Empuxa\LocaleViaApi;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class LocaleViaApiServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('locale-via-api')
            ->hasConfigFile();
    }
}
