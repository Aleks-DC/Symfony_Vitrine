<?php
namespace App\Controller;

use App\Service\HomepageProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(HomepageProvider $provider): Response
    {
        return $this->render('home/index.html.twig', $provider->get());
    }
}
