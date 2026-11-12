<?php

namespace NadecoLlms\Middlewares;

use Plenty\Plugin\Http\Request;
use Plenty\Plugin\Middleware;
use Symfony\Component\HttpFoundation\Response;

/**
 * Verhindert, dass reine Tracking-Parameter (ReferrerID, gclid, utm_* ...)
 * die SEO-Angaben einer Seite verändern.
 *
 * Ceres setzt bei jeder URL mit Query-String robots auf "noindex" und
 * übernimmt den Query-String in den Canonical. Weil der Shop-Cache
 * (ShopBooster) die Fassung mit ?ReferrerID=... auch für die saubere URL
 * ausliefert, landeten saubere Seiten zeitweise mit noindex im Index.
 *
 * Diese Middleware sorgt dafür, dass eine Seite mit Tracking-Parametern
 * exakt dieselben Angaben (robots, canonical, og:url, hreflang) hat wie
 * die saubere URL. Inhalts-Parameter (Filter, Sortierung, Suche, Seiten
 * ab 5) bleiben unverändert.
 */
class SeoParamMiddleware extends Middleware
{
    /** Reine Tracking-Parameter (klein geschrieben). utm_* wird separat geprüft. */
    const TRACKING = [
        'referrerid', 'gclid', 'gbraid', 'wbraid', 'gad_source', 'gad_campaignid',
        'dclid', 'fbclid', 'msclkid', 'srsltid', 'mc_cid', 'mc_eid',
        'ref', 'refid', 'refsrc', 'nosto',
    ];

    /** Pfad-Segmente, deren robots nie angefasst werden (Rechtstexte, System, Backend-noindex). */
    const SKIP_SEGMENTS = [
        'impressum', 'legal-disclosure', 'datenschutzerklaerung', 'privacy-policy',
        'agb', 'gtc', 'widerrufsrecht', 'cancellation-rights', 'widerrufsformular', 'cancellation-form',
        'search', 'basket', 'checkout', 'my-account', 'mein-konto', 'login', 'register',
        'wish-list', 'confirmation', 'bestellbestaetigung', 'retoure', 'rueckgabebedingungen',
        'produkte', 'zapfen', 'startseite-vorschau', 'rest', 'payment',
    ];

    /** Ab dieser Seitenzahl ist noindex gewollt (Ceres pagination.noIndex). */
    const PAGE_NOINDEX_FROM = 5;

    public function before(Request $request)
    {
    }

    public function after(Request $request, Response $response): Response
    {
        try {
            $uri = (string) $request->getRequestUri();
            $query = (string) parse_url($uri, PHP_URL_QUERY);
            if ($query === '') {
                return $response;
            }

            $type = (string) $response->headers->get('Content-Type');
            if ($type !== '' && stripos($type, 'text/html') === false) {
                return $response;
            }

            $content = $response->getContent();
            if (!is_string($content) || stripos($content, '<head') === false) {
                return $response;
            }

            // Nur Seiten mit Tracking-Parameter oder Folgeseiten (page>=2) anfassen
            $all = self::splitQuery($query);
            $rest = self::withoutTracking($all);
            $hasTracking = count($rest) !== count($all);
            $page = self::pageNumber($all);
            if (!$hasTracking && $page < 2) {
                return $response;
            }

            $new = $content;

            if ($hasTracking) {
                // 1. URLs in canonical, hreflang-alternate und og:url bereinigen
                $new = self::cleanTagUrls(
                    $new,
                    '/(<link\b[^>]*\brel=["\'](?:canonical|alternate)["\'][^>]*\bhref=["\'])([^"\']*)(["\'])/i'
                );
                $new = self::cleanTagUrls(
                    $new,
                    '/(<meta\b[^>]*\bproperty=["\']og:url["\'][^>]*\bcontent=["\'])([^"\']*)(["\'])/i'
                );

                // 2. robots: param-bedingtes "noindex" zurücksetzen, wenn nur Tracking übrig war
                if (self::robotsAllowed($uri, $rest)) {
                    $new = preg_replace(
                        '/(<meta\b[^>]*\bname=["\']robots["\'][^>]*\bcontent=["\'])noindex(["\'])/i',
                        '${1}ALL${2}',
                        $new
                    );
                }
            }

            // 3. Folgeseiten: Seitenzahl in Title und Meta-Description, damit Seite 1 eindeutig ist
            if ($page >= 2 && is_string($new)) {
                $new = self::addPageNumber($new, $page);
            }

            if (is_string($new) && $new !== $content) {
                $response->setContent($new);
            }
        } catch (\Throwable $e) {
            // Im Fehlerfall Seite unverändert ausliefern
        }

        return $response;
    }

    /** Seitenzahl aus page=N (0, wenn nicht vorhanden). */
    private static function pageNumber(array $pairs): int
    {
        foreach ($pairs as $pair) {
            if (strtolower($pair[0]) === 'page') {
                return (int) $pair[1];
            }
        }
        return 0;
    }

    /** Ergänzt "– Seite N" im <title> (vor " | Shopname") und "Seite N: " vor der Meta-Description. */
    private static function addPageNumber(string $html, int $page): string
    {
        $label = 'Seite ' . $page;

        $m = [];
        if (preg_match('/<title>([^<]*)<\/title>/i', $html, $m) && stripos($m[1], $label) === false) {
            $title = $m[1];
            $pos = strrpos($title, ' | ');
            $newTitle = $pos === false
                ? $title . ' – ' . $label
                : substr($title, 0, $pos) . ' – ' . $label . substr($title, $pos);
            $html = str_replace($m[0], '<title>' . $newTitle . '</title>', $html);
        }

        $m = [];
        if (preg_match('/(<meta\b[^>]*\bname=["\']description["\'][^>]*\bcontent=["\'])([^"\']*)(["\'])/i', $html, $m)
            && stripos($m[2], $label) === false) {
            $html = str_replace($m[0], $m[1] . $label . ': ' . $m[2] . $m[3], $html);
        }

        return $html;
    }

    /** Ersetzt in allen Treffern des Musters die URL (Gruppe 2) durch die bereinigte URL. */
    private static function cleanTagUrls(string $html, string $pattern): string
    {
        $matches = [];
        if (!preg_match_all($pattern, $html, $matches, PREG_SET_ORDER)) {
            return $html;
        }
        foreach ($matches as $m) {
            $clean = self::cleanUrl($m[2]);
            if ($clean !== $m[2]) {
                $html = str_replace($m[0], $m[1] . $clean . $m[3], $html);
            }
        }
        return $html;
    }

    /** Darf robots für diese Anfrage zurückgesetzt werden? */
    private static function robotsAllowed(string $uri, array $rest): bool
    {
        $path = strtolower((string) parse_url($uri, PHP_URL_PATH));
        foreach (explode('/', trim($path, '/')) as $segment) {
            if (in_array($segment, self::SKIP_SEGMENTS, true)) {
                return false;
            }
        }

        if (count($rest) === 0) {
            return true;
        }

        // Nur page=N übrig: wie die Folgeseite ohne Tracking behandeln
        if (count($rest) === 1 && strtolower($rest[0][0]) === 'page') {
            $page = (int) $rest[0][1];
            return $page > 0 && $page < self::PAGE_NOINDEX_FROM;
        }

        return false;
    }

    /** Entfernt Tracking-Parameter aus einer (ggf. HTML-kodierten) URL. */
    public static function cleanUrl(string $url): string
    {
        $pos = strpos($url, '?');
        if ($pos === false) {
            return $url;
        }

        $base = substr($url, 0, $pos);
        $query = substr($url, $pos + 1);
        $hash = '';
        $hashPos = strpos($query, '#');
        if ($hashPos !== false) {
            $hash = substr($query, $hashPos);
            $query = substr($query, 0, $hashPos);
        }

        $encoded = strpos($query, '&amp;') !== false;
        $query = str_replace('&amp;', '&', $query);

        $rest = self::withoutTracking(self::splitQuery($query));
        if (count($rest) === 0) {
            return $base . $hash;
        }

        $parts = [];
        foreach ($rest as $pair) {
            $parts[] = $pair[2];
        }

        return $base . '?' . implode($encoded ? '&amp;' : '&', $parts) . $hash;
    }

    /** @return array Liste aus [schlüssel, wert, original] */
    private static function splitQuery(string $query): array
    {
        $out = [];
        foreach (explode('&', $query) as $pair) {
            if ($pair === '') {
                continue;
            }
            $eq = strpos($pair, '=');
            $key = $eq === false ? $pair : substr($pair, 0, $eq);
            $value = $eq === false ? '' : substr($pair, $eq + 1);
            $out[] = [urldecode($key), urldecode($value), $pair];
        }
        return $out;
    }

    private static function withoutTracking(array $pairs): array
    {
        $out = [];
        foreach ($pairs as $pair) {
            $key = strtolower($pair[0]);
            if (in_array($key, self::TRACKING, true) || strpos($key, 'utm_') === 0) {
                continue;
            }
            $out[] = $pair;
        }
        return $out;
    }
}
