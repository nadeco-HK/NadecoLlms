<?php

namespace NadecoLlms\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Response;

/**
 * Liefert die IndexNow-Schlüsseldatei aus (Bing, Yandex, Seznam u. a.).
 */
class IndexNowController extends Controller
{
    const KEY = '741f9f45ff260b840354b262aa2fdf66';

    public function show(Response $response)
    {
        return $response->make(self::KEY, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
    }
}
