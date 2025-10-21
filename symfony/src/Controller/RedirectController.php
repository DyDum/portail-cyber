<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RedirectController extends AbstractController
{
    #[Route('/', name: 'homepage')]
    public function index(): Response
    {
        $user = $this->getUser();

        if ($user) {
            // Utilisateur connecté : dashboard
            return $this->redirectToRoute('dashboard');
        }

        // Sinon : login
        return $this->redirectToRoute('app_login');
    }
}
