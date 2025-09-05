<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // --- Fake data for now (replace later) ---
        $stats = [
            ['label' => 'Projects delivered', 'value' => 42],
            ['label' => 'Happy clients', 'value' => 15],
            ['label' => 'Years experience', 'value' => 5],
        ];

        $projects = [
            ['title' => 'Project A', 'excerpt' => 'Short desc A'],
            ['title' => 'Project B', 'excerpt' => 'Short desc B'],
        ];

        $testimonials = [
            ['author' => 'Jane Doe', 'quote' => 'Top notch!'],
            ['author' => 'John Doe', 'quote' => 'Very professional.'],
        ];

        $pricing = [
            ['name' => 'Starter', 'price' => 990],
            ['name' => 'Pro', 'price' => 2490],
        ];

        $selectedProject = null; // or one of the $projects

        return $this->render('home/index.html.twig', [
            'stats'           => $stats,
            'projects'        => $projects,
            'testimonials'    => $testimonials,
            'pricing'         => $pricing,
            'selectedProject' => $selectedProject,
        ]);
    }
}
