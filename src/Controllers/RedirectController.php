<?php

namespace NadecoLlms\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Http\Response;

/**
 * Dauerhafte 301-Weiterleitungen für verschobene oder umbenannte Shop-Adressen.
 * Neue Weiterleitung: Eintrag in MAP ergänzen und dieselbe Adresse
 * in NadecoLlmsRouteServiceProvider registrieren.
 */
class RedirectController extends Controller
{
    const BASE = 'https://www.nadeco.de';

    const MAP = [
        '/naturdeko/kokosnuss-deko/palmenblaetter-deko' => '/naturdeko/palmenblaetter-deko',
    ];

    public function redirect(Request $request, Response $response)
    {
        $path = '/' . trim((string) parse_url($request->getRequestUri(), PHP_URL_PATH), '/');
        $target = self::MAP[$path] ?? '/';
        $query = (string) parse_url($request->getRequestUri(), PHP_URL_QUERY);

        return $response->redirectTo(self::BASE . $target . ($query !== '' ? '?' . $query : ''), 301);
    }

    public static function paths(): array
    {
        return array_keys(self::MAP);
    }
}
