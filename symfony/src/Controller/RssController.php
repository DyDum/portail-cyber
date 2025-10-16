<?php
namespace App\Controller;

use FeedIo\FeedIo;
use FeedIo\Adapter\Http\Client as FeedIoHttpClient;
use GuzzleHttp\Client as GuzzleClient;
use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Log\NullLogger;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RssController extends AbstractController
{
    #[Route('/rss', name: 'rss_list')]
    public function index(Request $request): Response
    {
        $feedIndex = $request->query->getInt('feed', -1);

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

        if ($feedIndex >= 0 && !isset($feeds[$feedIndex])) {
            return $this->render('error.html.twig', [
                'message' => "Le flux RSS demandé n'existe pas.",
                'backUrl' => $this->generateUrl('rss_list'),
            ]);
        }

        // Sélectionne un seul flux si feed est précisé
        if ($feedIndex >= 0) {
            $feeds = [$feeds[$feedIndex]];
        }

        // Initialisation de FeedIo
        $psr17Factory = new Psr17Factory();
        $httpClient = new FeedIoHttpClient(
            new \Http\Adapter\Guzzle7\Client(new GuzzleClient()),
            $psr17Factory,
            $psr17Factory
        );

        $logger = new NullLogger();
        $feedIo = new FeedIo($httpClient, $logger);

        $articles = [];
        foreach ($feeds as $feedData) {
            try {
                $result = $feedIo->read($feedData['url']);
                foreach ($result->getFeed() as $item) {
                    $articles[] = [
                        'source' => $feedData['name'],
                        'title' => $item->getTitle(),
                        'link' => $item->getLink(),
                        'date' => $item->getLastModified()?->format('Y-m-d') ?? '',
                    ];
                }
            } catch (\Throwable $e) {
                continue;
            }
        }

        $favoritesFile = __DIR__ . '/../../data/favorites_' . $this->getUser()->getUserIdentifier() . '.json';
        $favorites = file_exists($favoritesFile)
            ? json_decode(file_get_contents($favoritesFile), true)['favorites']
            : [];

        usort($articles, fn($a, $b) => strcmp($b['date'], $a['date']));

        return $this->render('rss/index.html.twig', [
            'articles' => $articles,
            'feeds' => $feeds,
            'favorites' => $favorites,
            'selectedFeed' => $feedIndex >= 0 ? $feedIndex : null,
            'invalidFeed' => null,
        ]);
    }
}
