<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'dashboard')]
    public function index(Request $request): Response
    {
        $categoryName = $request->query->get('category');

        $categoriesFile = __DIR__ . '/../../data/categories.json';
        $categories = file_exists($categoriesFile)
            ? json_decode(file_get_contents($categoriesFile), true)
            : [];

        // Cas d’erreur : fichier vide ou mal formé
        if (!is_array($categories)) {
            return $this->render('error.html.twig', [
                'message' => "Le fichier des catégories est introuvable ou invalide.",
                'backUrl' => $this->generateUrl('dashboard'),
            ]);
        }

        // Si une catégorie spécifique est demandée
        if ($categoryName !== null) {
            if (!isset($categories[$categoryName])) {
                // Cas d’erreur : clé inexistante
                return $this->render('error.html.twig', [
                    'message' => "La catégorie demandée (« $categoryName ») n'existe pas.",
                    'backUrl' => $this->generateUrl('dashboard'),
                ]);
            }

            // On n’affiche que la catégorie demandée
            $selectedCategory = [$categoryName => $categories[$categoryName]];
            $categories = $selectedCategory;
        } else {
            $selectedCategory = null;
        }

        return $this->render('dashboard/index.html.twig', [
            'categories_dashboard' => $categories,
            'selectedCategory' => $categoryName,
        ]);
    }
}
