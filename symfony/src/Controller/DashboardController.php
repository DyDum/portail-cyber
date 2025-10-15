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
        $outilName = $request->query->get('outil');

        $outilsFile = __DIR__ . '/../../data/outils.json';
        $outils = file_exists($outilsFile)
            ? json_decode(file_get_contents($outilsFile), true)
            : [];

        // Cas d’erreur : fichier vide ou mal formé
        if (!is_array($outils)) {
            return $this->render('error.html.twig', [
                'message' => "Le fichier des catégories est introuvable ou invalide.",
                'backUrl' => $this->generateUrl('dashboard'),
            ]);
        }

        // Si une catégorie spécifique est demandée
        if ($outilName !== null) {
            if (!isset($outils[$outilName])) {
                // Cas d’erreur : clé inexistante
                return $this->render('error.html.twig', [
                    'message' => "La catégorie demandée (« $outilName ») n'existe pas.",
                    'backUrl' => $this->generateUrl('dashboard'),
                ]);
            }

            // On n’affiche que la catégorie demandée
            $selectedoutil = [$outilName => $outils[$outilName]];
            $outils = $selectedoutil;
        } else {
            $selectedoutil = null;
        }

        return $this->render('dashboard/index.html.twig', [
            'outils_dashboard' => $outils,
            'selectedoutil' => $outilName,
        ]);
    }
}
