<?php
namespace App\Twig;

use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class AppGlobals extends AbstractExtension implements GlobalsInterface
{
    private Security $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function getGlobals(): array
    {
        $user = $this->security->getUser();
        if ($user) {
            // Fichiers JSON
            $outilsFile = __DIR__ . '/../../data/outils.json';
            $rssFile = __DIR__ . '/../../data/rss.json';

            $outils = file_exists($outilsFile)
                ? json_decode(file_get_contents($outilsFile), true)
                : [];

            $rssFeeds = file_exists($rssFile)
                ? json_decode(file_get_contents($rssFile), true)
                : [];

            // Favoris personnalisés par utilisateur
            $favoriteFeeds = [];

            $username = $user->getUserIdentifier();
            $favoritesFile = __DIR__ . '/../../data/favorites_' . $username . '.json';
            $favorites = file_exists($favoritesFile)
                ? json_decode(file_get_contents($favoritesFile), true)['favorites'] ?? []
                : [];
            sort($favorites, SORT_NUMERIC);

            foreach ($favorites as $index) {
                if (isset($rssFeeds[$index])) {
                    $favoriteFeeds[] = [
                        'index' => $index,
                        'name' => $rssFeeds[$index]['name'],
                    ];
                }
            }
            return [
                'outils' => $outils,
                'rssFeeds' => $rssFeeds,
                'favoriteFeeds' => $favoriteFeeds,
                'favorites' => $favorites,
            ];
        }else{
            return [];
        }

        
    }
}
