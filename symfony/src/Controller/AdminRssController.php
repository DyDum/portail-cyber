<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminRssController extends AbstractController
{
    private string $rssFile;

    public function __construct()
    {
        $this->rssFile = __DIR__ . '/../../data/rss.json';
    }

    #[Route('/admin/rss/add', name: 'admin_rss_add', methods: ['POST'])]
    public function addRss(Request $request): Response
    {
        $name = trim($request->request->get('name'));
        $url = trim($request->request->get('url'));

        if ($name && $url) {
            $feeds = $this->loadFeeds();
            $feeds[] = ['name' => $name, 'url' => $url];
            $this->saveFeeds($feeds);
            $this->addFlash('success', "Flux RSS '$name' ajouté !");
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    #[Route('/admin/rss/delete', name: 'admin_rss_delete', methods: ['GET'])]
    public function deleteRss(Request $request): Response
    {
        $index = (int)$request->query->get('id');
        $feeds = $this->loadFeeds();

        if (isset($feeds[$index])) {
            $deleted = $feeds[$index]['name'];
            unset($feeds[$index]);
            $feeds = array_values($feeds);
            $this->saveFeeds($feeds);
            $this->addFlash('warning', "Flux RSS '$deleted' supprimé !");
        }

        return $this->redirectToRoute('admin_dashboard');
    }

    private function loadFeeds(): array
    {
        if (!file_exists($this->rssFile)) return [];
        return json_decode(file_get_contents($this->rssFile), true) ?? [];
    }

    private function saveFeeds(array $feeds): void
    {
        file_put_contents($this->rssFile, json_encode($feeds, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
