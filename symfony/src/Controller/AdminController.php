<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends AbstractController
{
    private string $dataFile;

    public function __construct()
    {
        $this->dataFile = __DIR__ . '/../../data/categories.json';
    }

    #[Route('/admin', name: 'admin_dashboard', methods: ['GET', 'POST'])]
    public function index(Request $request): Response
    {
        $data = $this->loadData();
        $rssFile = __DIR__ . '/../../data/rss.json';
        $rssFeeds = file_exists($rssFile) ? json_decode(file_get_contents($rssFile), true) : [];

        // ---- Ajout d’un bouton ----
        if ($request->request->has('add_button')) {
            $category = trim($request->request->get('category'));
            $name = trim($request->request->get('name'));
            $url = trim($request->request->get('url'));

            if ($category && $name && $url) {
                $data[$category][] = ['name' => $name, 'url' => $url];
                $this->saveData($data);
                $this->addFlash('success', "Bouton ajouté dans $category !");
            }
            return $this->redirectToRoute('admin_dashboard');
        }

        // ---- Ajout d’une catégorie ----
        if ($request->request->has('add_category')) {
            $newCategory = trim($request->request->get('new_category'));
            if ($newCategory && !isset($data[$newCategory])) {
                $data[$newCategory] = [];
                $this->saveData($data);
                $this->addFlash('success', "Catégorie '$newCategory' ajoutée !");
            }
            return $this->redirectToRoute('admin_dashboard');
        }

        // ---- Suppression d’une catégorie ----
        if ($request->query->has('delete_category')) {
            $category = $request->query->get('delete_category');
            if (isset($data[$category])) {
                unset($data[$category]);
                $this->saveData($data);
                $this->addFlash('warning', "Catégorie '$category' supprimée !");
            }
            return $this->redirectToRoute('admin_dashboard');
        }

        // ---- Suppression d’un bouton ----
        if ($request->query->has('delete_button')) {
            $cat = $request->query->get('cat');
            $index = (int)$request->query->get('delete_button');
            if (isset($data[$cat][$index])) {
                unset($data[$cat][$index]);
                $data[$cat] = array_values($data[$cat]); // Réindexe
                $this->saveData($data);
                $this->addFlash('warning', "Bouton supprimé de $cat !");
            }
            return $this->redirectToRoute('admin_dashboard');
        }

        return $this->render('admin/index.html.twig', [
            'data' => $data,
            'rssFeeds' => $rssFeeds,
        ]);
    }

    private function loadData(): array
    {
        if (!file_exists($this->dataFile)) return [];
        return json_decode(file_get_contents($this->dataFile), true) ?? [];
    }

    private function saveData(array $data): void
    {
        file_put_contents($this->dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
