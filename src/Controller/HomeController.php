<?php
declare(strict_types=1);

namespace App\Controller;

use App\Service\Content\ContentResolver;
use App\Service\HomepageProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function __invoke(Request $request, HomepageProvider $provider, ContentResolver $resolver): Response
    {
        // Ton view model habituel (inclut déjà 'contact' venant de contact.yaml)
        $vm = $provider->getViewModel();
        $vm['components'] = $resolver->componentsFor('home');

        // Récupération des flashs posés par ContactController
        $bag = $request->getSession()->getFlashBag();
        $vm['contactValues']  = $bag->get('contact.values')[0]  ?? [];
        $vm['contactErrors']  = $bag->get('contact.errors')[0]  ?? [];
        $vm['contactSuccess'] = (bool)($bag->get('contact.success')[0] ?? false);

        return $this->render('home/index.html.twig', $vm);
    }
}
