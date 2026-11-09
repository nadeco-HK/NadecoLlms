<?php

namespace NadecoLlms\Providers;

use NadecoLlms\Middlewares\SeoParamMiddleware;
use Plenty\Plugin\ServiceProvider;

class NadecoLlmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->getApplication()->register(NadecoLlmsRouteServiceProvider::class);
        $this->addGlobalMiddleware(SeoParamMiddleware::class);
    }
}
