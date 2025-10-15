<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppGlobals extends AbstractExtension implements GlobalsInterface
{
    public function getGlobals(): array
    {
        // Lecture du fichier des catégories
        $categoriesFile = __DIR__ . '/../../data/categories.json';
        $categories = file_exists($categoriesFile)
            ? json_decode(file_get_contents($categoriesFile), true)
            : [];

        // Lecture du fichier des flux RSS
        $rssFile = __DIR__ . '/../../data/rss.json';
        $rssFeeds = file_exists($rssFile)
            ? json_decode(file_get_contents($rssFile), true)
            : [];

        return [
            'categories' => $categories,
            'rssFeeds' => $rssFeeds,
        ];
    }
}
