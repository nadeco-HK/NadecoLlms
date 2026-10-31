<?php

namespace NadecoLlms\Controllers;

use Plenty\Plugin\Controller;
use Plenty\Plugin\Http\Response;

class LlmsController extends Controller
{
    const CONTENT = <<<'LLMS_TXT'
# NaDeco – Natürliche Dekoration & Trockenblumen

> NaDeco (nadeco.de) ist ein Online-Shop für echte Naturmaterialien zum Dekorieren, Basteln und Binden: Trockenblumen, Muscheln, Zapfen, Hölzer, exotische Früchte sowie Floristik- und Saisondekoration. Seit 2011 Versandhandel aus eigenem Lager in Munster (Lüneburger Heide). Privatkunden und gewerbliche Kunden (Floristen, Dekorateure, Händler).

Wichtige Fakten:
- Betreiber: NaDeco Nils Daniel Steinbuß, Wolfsloh 9, 29633 Munster, Deutschland
- Telefon: +49 5192 986990 · E-Mail: info@nadeco.de
- Versand aus eigenem Lager in Munster mit DHL, auf Wunsch auch an Packstationen
- Großhandel: gewerbliche Kunden bestellen nach Freischaltung eines Händlerkontos zu Nettopreisen in Verpackungseinheiten, Mindestbestellwert 200 €
- Naturprodukte sind Unikate: Größen, Farben und Mengen sind Circa-Angaben

## Hauptkategorien

- [Naturdeko](https://www.nadeco.de/naturdeko): echte Naturmaterialien aus aller Welt
- [Floristik & Dekoration](https://www.nadeco.de/floristik-dekoration): Glasdeko, Pflanzgefäße, Bänder, Kerzen, Metall- und Rostdeko
- [Saisonale Deko](https://www.nadeco.de/saisonale-dekoration): Advent und Weihnachten, Ostern, Frühling, Sommer, Herbst, Hochzeit
- [Haus, Garten & Freizeit](https://www.nadeco.de/haus-garten-und-freizeit): Wohnen, Garten-Deko, Partydeko, Boho-Deko
- [Sale](https://www.nadeco.de/nadeco-sale-und-sonderangebote): reduzierte Artikel und B-Ware

## Naturdeko im Detail

- [Trockenblumen](https://www.nadeco.de/naturdeko/trockenblumen): Gräser, Blüten, Blätter und fertige Sträuße
- [Muscheln & Schnecken](https://www.nadeco.de/naturdeko/muscheln-und-schnecken): Seesterne, Seeigel, Perlmutt- und Capizscheiben
- [Hölzer & Wurzeln](https://www.nadeco.de/naturdeko/hoelzer-wurzeln): Treibholz, Dekoäste, Rinde, Holzscheiben
- [Tannenzapfen zum Basteln](https://www.nadeco.de/naturdeko/tannenzapfen-zum-basteln): Kiefer, Lärche, Pinie, Fichte
- [Exotische Zapfen & Früchte](https://www.nadeco.de/naturdeko/exotische-zapfen-fruechte): Lotuskapseln, Banksia, Wabenfrucht
- [Getrocknete Fruchtscheiben](https://www.nadeco.de/naturdeko/getrocknete-fruchtscheiben-und-fruechte): Orangen, Zitronen, Äpfel
- [Naturdekorationen](https://www.nadeco.de/naturdeko/naturdekorationen): Moos, Kränze, Naturbündel
- [Kokosnuss-Deko](https://www.nadeco.de/naturdeko/kokosnuss-deko): Schalen, Sterne, Kokosblätter
- [Deko-Eier](https://www.nadeco.de/naturdeko/deko-eier-ausgeblasen-und-gereinigt): ausgeblasen und gereinigt
- [Hörner & Schädel](https://www.nadeco.de/naturdeko/hoerner-schaedel)
- [Sand, Kies & Steine](https://www.nadeco.de/naturdeko/dekosand-dekokies-dekosteine)
- [Terrarium- & Aquariumdeko](https://www.nadeco.de/naturdeko/terrarium-aqarium-deko)
- [Tierfelle](https://www.nadeco.de/naturdeko/tierfelle)
- [Vogel- & Nagetierzubehör](https://www.nadeco.de/naturdeko/vogel-nagetier-zubehoer)

## Service

- [Großhandel für Gewerbe](https://www.nadeco.de/grosshandel-b2b): Händlerkonto, Nettopreise, Verpackungseinheiten
- [Sitemap](https://www.nadeco.de/sitemap.xml)
LLMS_TXT;

    public function show(Response $response)
    {
        return $response->make(
            self::CONTENT . "\n",
            200,
            [
                'Content-Type'  => 'text/plain; charset=utf-8',
                'Cache-Control' => 'public, max-age=86400',
                'X-Robots-Tag'  => 'noindex'
            ]
        );
    }
}
