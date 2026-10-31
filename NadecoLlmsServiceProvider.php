<?php

namespace NadecoLlms\Providers;

use Plenty\Plugin\ServiceProvider;

class NadecoLlmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->getApplication()->register(NadecoLlmsRouteServiceProvider::class);
    }
}
