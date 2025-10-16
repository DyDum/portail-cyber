<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FavoriteController extends AbstractController
{
    private function getFavoritesFile(): string
    {
        $username = $this->getUser()->getUserIdentifier();
        return __DIR__ . '/../../data/favorites_' . $username . '.json';
    }

    private function loadFavorites(): array
    {
        $file = $this->getFavoritesFile();
        if (!file_exists($file)) {
            return ['favorites' => []];
        }

        $data = json_decode(file_get_contents($file), true);
        return is_array($data) ? $data : ['favorites' => []];
    }

    private function saveFavorites(array $favorites): void
    {
        file_put_contents($this->getFavoritesFile(), json_encode($favorites, JSON_PRETTY_PRINT));
    }

    #[Route('/favorites/toggle/{feedIndex}', name: 'toggle_favorite', methods: ['POST'])]
    public function toggleFavorite(int $feedIndex): Response
    {
        $favorites = $this->loadFavorites();

        if (in_array($feedIndex, $favorites['favorites'])) {
            $favorites['favorites'] = array_values(array_diff($favorites['favorites'], [$feedIndex]));
            $status = 'removed';
        } else {
            $favorites['favorites'][] = $feedIndex;
            $status = 'added';
        }

        $this->saveFavorites($favorites);

        return $this->json([
            'success' => true,
            'status' => $status,
            'favorites' => $favorites['favorites']
        ]);
    }

    #[Route('/rss/favorites', name: 'rss_favorites')]
    public function listFavorites(): Response
    {
        $rssFile = __DIR__ . '/../../data/rss.json';
        $favorites = $this->loadFavorites()['favorites'];

        $feeds = file_exists($rssFile)
            ? json_decode(file_get_contents($rssFile), true)
            : [];

        $favoriteFeeds = [];
        foreach ($favorites as $index) {
            if (isset($feeds[$index])) {
                $favoriteFeeds[] = $feeds[$index];
            }
        }

        return $this->render('rss/favorites.html.twig', [
            'feeds' => $favoriteFeeds,
        ]);
    }
}
