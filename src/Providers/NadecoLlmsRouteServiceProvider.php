<?php

namespace NadecoLlms\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;

class NadecoLlmsRouteServiceProvider extends RouteServiceProvider
{
    public function map(Router $router)
    {
        $router->get('llms.txt', 'NadecoLlms\Controllers\LlmsController@show');
    }
}
