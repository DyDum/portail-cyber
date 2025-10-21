<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;

class FavoriteController extends AbstractController
{
    #[Route('/rss/favorites', name: 'rss_favorites')]
    public function favorites(Request $request, RssController $rssController): Response
    {
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $favIndex = $request->query->getInt('fav', -1);
        $username = $user->getUserIdentifier();
        $favoritesFile = __DIR__ . '/../../data/favorites_' . $username . '.json';
        $rssFile = __DIR__ . '/../../data/rss.json';

        if (!file_exists($rssFile)) {
            return $this->render('error.html.twig', [
                'message' => "Le fichier RSS n'existe pas ou n'est pas accessible.",
                'backUrl' => $this->generateUrl('dashboard'),
            ]);
        }

        $feeds = json_decode(file_get_contents($rssFile), true);
        if (!is_array($feeds)) {
            return $this->render('error.html.twig', [
                'message' => "Le format du fichier RSS est invalide.",
                'backUrl' => $this->generateUrl('dashboard'),
            ]);
        }

        $favorites = file_exists($favoritesFile)
            ? json_decode(file_get_contents($favoritesFile), true)['favorites'] ?? []
            : [];

        if (empty($favorites)) {
            return $this->render('rss/favorites.html.twig', [
                'articles' => [],
                'feeds' => [],
                'selectedFav' => null,
                'noFavorites' => true,
            ]);
        }

        // Sélection des flux favoris
        $favFeeds = [];
        foreach ($favorites as $index) {
            if (isset($feeds[$index])) {
                $favFeeds[] = $feeds[$index];
            }
        }

        // Si un favori spécifique est demandé, on le filtre
        if ($favIndex >= 0 && isset($feeds[$favIndex]) && in_array($favIndex, $favorites)) {
            $favFeeds = [$feeds[$favIndex]];
        }

        // 🔹 Récupération des articles via RssController
        $articles = $rssController->fetchArticles($favFeeds, $favIndex);

        $favFeedsList = [];
        foreach ($favorites as $index) {
            if (isset($feeds[$index])) {
                $favFeedsList[] = ['url' => $feeds[$index]['url'], 'name' => $feeds[$index]['name'], 'index' => $index];
            }
        }

        return $this->render('rss/favorites.html.twig', [
            'articles' => $articles,
            'feeds' => $favFeeds,
            'favs' => $favFeedsList,
            'selectedFav' => $favIndex >= 0 ? $favIndex : null,
            'noFavorites' => false,
        ]);
    }

    #[Route('/rss/favorites/toggle/{index}', name: 'rss_toggle_favorite', methods: ['POST'])]
    public function toggleFavorite(int $index): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], 403);
        }

        $username = $user->getUserIdentifier();
        $favoritesFile = __DIR__ . '/../../data/favorites_' . $username . '.json';

        $favorites = [];
        if (file_exists($favoritesFile)) {
            $data = json_decode(file_get_contents($favoritesFile), true);
            $favorites = $data['favorites'] ?? [];
        }

        // Ajoute ou supprime
        if (in_array($index, $favorites)) {
            $favorites = array_values(array_diff($favorites, [$index]));
            $action = 'removed';
        } else {
            $favorites[] = $index;
            $action = 'added';
        }

        // Sauvegarde
        file_put_contents($favoritesFile, json_encode(['favorites' => $favorites], JSON_PRETTY_PRINT));

        return new JsonResponse([
            'status' => 'success',
            'action' => $action,
            'favorites' => $favorites,
        ]);
    }
}
