<?php

namespace NadecoLlms\Providers;

use Plenty\Plugin\RouteServiceProvider;
use Plenty\Plugin\Routing\Router;
use NadecoLlms\Controllers\RedirectController;

class NadecoLlmsRouteServiceProvider extends RouteServiceProvider
{
    public function map(Router $router)
    {
        $router->get('llms.txt', 'NadecoLlms\Controllers\LlmsController@show');
        $router->get('indexnow-741f9f45ff260b840354b262aa2fdf66', 'NadecoLlms\Controllers\IndexNowController@show');

        foreach (RedirectController::paths() as $path) {
            $router->get(ltrim($path, '/'), 'NadecoLlms\Controllers\RedirectController@redirect');
        }
    }
}
