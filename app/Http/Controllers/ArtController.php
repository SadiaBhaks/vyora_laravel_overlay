<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Generates original, deterministic SVG artwork from a seed. It is the guaranteed fallback for every image slot
 * (it can never 404 or hotlink-break) and is clearly illustrative artwork, not product photography.
 * Replace with real photography via Admin > Products > Images.
 */
class ArtController extends Controller
{
    private const HUES = [
        'fashion' => 18, 'electronics' => 215, 'accessories' => 38, 'beauty' => 345,
        'home' => 95, 'fitness' => 160, 'lifestyle' => 270, 'smart-living' => 190,
    ];

    public function __invoke(Request $request): Response
    {
        $seed = substr(preg_replace('/[^a-z0-9\-]/i', '', (string) $request->query('seed', 'vyora')), 0, 80) ?: 'vyora';
        $cat = substr(preg_replace('/[^a-z\-]/', '', (string) $request->query('c', 'lifestyle')), 0, 30);
        $shape = $request->query('w');
        [$w, $h] = match ($shape) { 'wide' => [1600, 900], 'tall' => [800, 1000], default => [800, 800] };

        $rand = $this->rng(crc32($seed));
        $baseHue = self::HUES[$cat] ?? 250;
        $hue = ($baseHue + (int) ($rand() * 36) - 18 + 360) % 360;
        $dark = $rand() > 0.55;

        $bg1 = $dark ? $this->hsl($hue, 22, 14) : $this->hsl($hue, 30, 90);
        $bg2 = $dark ? $this->hsl(($hue + 25) % 360, 30, 24) : $this->hsl(($hue + 25) % 360, 38, 80);
        $ink = $dark ? $this->hsl($hue, 25, 92) : $this->hsl($hue, 35, 14);
        $mid = $this->hsl(($hue + 12) % 360, 45, $dark ? 42 : 58);
        $pop = $this->hsl(($hue + 150) % 360, 70, $dark ? 64 : 52);

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 '.$w.' '.$h.'" preserveAspectRatio="xMidYMid slice" role="img">';
        $svg .= '<defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="'.$bg1.'"/><stop offset="1" stop-color="'.$bg2.'"/></linearGradient>';
        $svg .= '<filter id="b"><feGaussianBlur stdDeviation="'.(int) ($w / 40).'"/></filter></defs>';
        $svg .= '<rect width="'.$w.'" height="'.$h.'" fill="url(#g)"/>';

        // soft atmospheric blobs
        for ($i = 0; $i < 3; $i++) {
            $svg .= sprintf('<circle cx="%d" cy="%d" r="%d" fill="%s" opacity=".35" filter="url(#b)"/>',
                $rand() * $w, $rand() * $h, $w * (0.18 + $rand() * 0.2), $i % 2 ? $pop : $mid);
        }
        // architectural rings / slabs
        $cx = $w * (0.42 + $rand() * 0.16);
        $cy = $h * (0.44 + $rand() * 0.12);
        $r = min($w, $h) * (0.30 + $rand() * 0.08);
        $svg .= sprintf('<circle cx="%d" cy="%d" r="%d" fill="none" stroke="%s" stroke-width="%d" opacity=".25"/>', $cx, $cy, $r * 1.25, $ink, max(2, $w / 200));
        $svg .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" rx="%d" fill="%s" opacity=".14" transform="rotate(%d %d %d)"/>',
            $cx - $r, $cy - $r * 0.8, $r * 2, $r * 1.6, $r * 0.12, $ink, (int) ($rand() * 30) - 15, $cx, $cy);

        $svg .= $this->glyph($cat, $cx, $cy, $r, $ink, $mid, $pop, $dark);

        // ground shadow
        $svg .= sprintf('<ellipse cx="%d" cy="%d" rx="%d" ry="%d" fill="%s" opacity=".18" filter="url(#b)"/>', $cx, $cy + $r * 1.05, $r * 0.9, $r * 0.12, $ink);
        $svg .= '</svg>';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    private function rng(int $seed): \Closure
    {
        $state = $seed ?: 1;

        return function () use (&$state) {
            $state = ($state * 1103515245 + 12345) & 0x7fffffff;

            return $state / 0x7fffffff;
        };
    }

    private function hsl(int $h, int $s, int $l): string
    {
        return "hsl($h $s% $l%)";
    }

    private function glyph(string $cat, float $x, float $y, float $r, string $ink, string $mid, string $pop, bool $dark): string
    {
        $o = '';
        switch ($cat) {
            case 'electronics': // over-ear headphones
                $o .= sprintf('<path d="M%d %d a%d %d 0 0 1 %d 0" fill="none" stroke="%s" stroke-width="%d" stroke-linecap="round"/>', $x - $r * .62, $y, $r * .62, $r * .75, $r * 1.24, $ink, $r * .1);
                $o .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" rx="%d" fill="%s"/>', $x - $r * .82, $y - $r * .05, $r * .38, $r * .72, $r * .16, $mid);
                $o .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" rx="%d" fill="%s"/>', $x + $r * .44, $y - $r * .05, $r * .38, $r * .72, $r * .16, $mid);
                $o .= sprintf('<circle cx="%d" cy="%d" r="%d" fill="%s"/>', $x + $r * .63, $y + $r * .3, $r * .06, $pop);
                break;
            case 'fashion': // jacket
                $o .= sprintf('<path d="M%d %d l%d %d l%d %d l%d %d l%d %d l%d %d l%d %d z" fill="%s"/>', $x - $r * .35, $y - $r * .7, -$r * .45, $r * .3, $r * .1, $r * .55, $r * .25, -$r * .1, 0, $r * .9, $r * .6, 0, 0, -$r * .9, $mid);
                $o .= sprintf('<path d="M%d %d l%d %d l%d %d l%d %d l%d %d l%d %d z" fill="%s" opacity=".9"/>', $x - $r * .35, $y - $r * .7, $r * .35, $r * .22, $r * .35, -$r * .22, $r * .45, $r * .3, -$r * .1, $r * .55, -$r * .25, -$r * .1, $mid);
                $o .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" fill="%s"/>', $x - $r * .015, $y - $r * .48, $r * .03, $r * 1.15, $pop);
                break;
            case 'accessories': // watch
                $o .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" rx="%d" fill="%s"/>', $x - $r * .2, $y - $r * .95, $r * .4, $r * 1.9, $r * .12, $ink);
                $o .= sprintf('<circle cx="%d" cy="%d" r="%d" fill="%s"/>', $x, $y, $r * .5, $mid);
                $o .= sprintf('<circle cx="%d" cy="%d" r="%d" fill="%s"/>', $x, $y, $r * .4, $dark ? '#0b0b0b' : '#f7f4ee');
                $o .= sprintf('<path d="M%d %d L%d %d M%d %d L%d %d" stroke="%s" stroke-width="%d" stroke-linecap="round"/>', $x, $y, $x, $y - $r * .28, $x, $y, $x + $r * .2, $y + $r * .08, $ink, $r * .04);
                $o .= sprintf('<circle cx="%d" cy="%d" r="%d" fill="%s"/>', $x, $y, $r * .03, $pop);
                break;
            case 'beauty': // bottle
                $o .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" rx="%d" fill="%s"/>', $x - $r * .13, $y - $r * .9, $r * .26, $r * .3, $r * .04, $ink);
                $o .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" rx="%d" fill="%s"/>', $x - $r * .38, $y - $r * .62, $r * .76, $r * 1.4, $r * .2, $mid);
                $o .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" rx="%d" fill="%s" opacity=".85"/>', $x - $r * .26, $y - $r * .2, $r * .52, $r * .6, $r * .06, $dark ? '#111' : '#faf6f0');
                $o .= sprintf('<circle cx="%d" cy="%d" r="%d" fill="%s"/>', $x, $y + $r * .1, $r * .07, $pop);
                break;
            case 'home': // lamp
                $o .= sprintf('<path d="M%d %d L%d %d L%d %d L%d %d z" fill="%s"/>', $x - $r * .2, $y - $r * .7, $x + $r * .2, $y - $r * .7, $x + $r * .5, $y - $r * .15, $x - $r * .5, $y - $r * .15, $mid);
                $o .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" fill="%s"/>', $x - $r * .03, $y - $r * .15, $r * .06, $r * .8, $ink);
                $o .= sprintf('<ellipse cx="%d" cy="%d" rx="%d" ry="%d" fill="%s"/>', $x, $y + $r * .68, $r * .32, $r * .07, $ink);
                $o .= sprintf('<circle cx="%d" cy="%d" r="%d" fill="%s" opacity=".7"/>', $x, $y - $r * .1, $r * .1, $pop);
                break;
            case 'fitness': // dumbbell
                foreach ([-1, 1] as $s) {
                    $o .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" rx="%d" fill="%s"/>', $x + $s * $r * .62 - $r * .1, $y - $r * .42, $r * .2, $r * .84, $r * .05, $ink);
                    $o .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" rx="%d" fill="%s"/>', $x + $s * $r * .43 - $r * .09, $y - $r * .55, $r * .18, $r * 1.1, $r * .05, $mid);
                }
                $o .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" fill="%s"/>', $x - $r * .45, $y - $r * .05, $r * .9, $r * .1, $pop);
                break;
            case 'smart-living': // smart speaker puck
                $o .= sprintf('<ellipse cx="%d" cy="%d" rx="%d" ry="%d" fill="%s"/>', $x, $y + $r * .35, $r * .62, $r * .16, $ink);
                $o .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" rx="%d" fill="%s"/>', $x - $r * .62, $y - $r * .35, $r * 1.24, $r * .7, $r * .3, $mid);
                $o .= sprintf('<ellipse cx="%d" cy="%d" rx="%d" ry="%d" fill="%s" opacity=".5"/>', $x, $y - $r * .35, $r * .62, $r * .18, $ink);
                $o .= sprintf('<circle cx="%d" cy="%d" r="%d" fill="%s"/>', $x, $y - $r * .35, $r * .07, $pop);
                break;
            default: // lifestyle tote
                $o .= sprintf('<path d="M%d %d a%d %d 0 0 1 %d 0" fill="none" stroke="%s" stroke-width="%d" stroke-linecap="round"/>', $x - $r * .28, $y - $r * .3, $r * .28, $r * .4, $r * .56, $ink, $r * .06);
                $o .= sprintf('<path d="M%d %d L%d %d L%d %d L%d %d z" fill="%s"/>', $x - $r * .55, $y - $r * .3, $x + $r * .55, $y - $r * .3, $x + $r * .7, $y + $r * .75, $x - $r * .7, $y + $r * .75, $mid);
                $o .= sprintf('<rect x="%d" y="%d" width="%d" height="%d" rx="%d" fill="%s"/>', $x - $r * .15, $y + $r * .05, $r * .3, $r * .3, $r * .05, $pop);
        }

        return $o;
    }
}
