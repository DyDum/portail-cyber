<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppGlobals extends AbstractExtension implements GlobalsInterface
{
    public function getGlobals(): array
    {
        // Lecture du fichier des catégories
        $outilsFile = __DIR__ . '/../../data/outils.json';
        $outils = file_exists($outilsFile)
            ? json_decode(file_get_contents($outilsFile), true)
            : [];

        // Lecture du fichier des flux RSS
        $rssFile = __DIR__ . '/../../data/rss.json';
        $rssFeeds = file_exists($rssFile)
            ? json_decode(file_get_contents($rssFile), true)
            : [];

        return [
            'outils' => $outils,
            'rssFeeds' => $rssFeeds,
        ];
    }
}
