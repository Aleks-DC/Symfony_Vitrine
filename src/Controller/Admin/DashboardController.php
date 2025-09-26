<?php

namespace App\Controller\Admin;

use App\Entity\Page;
use App\Entity\PageSection;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Dashboard, MenuItem};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
final class DashboardController extends AbstractDashboardController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator) {}

    public function index(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(PageCrudController::class) // ta page principale d’admin
            ->setAction('index')
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()->setTitle('Aleks_DC - Admin');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::section('Contenu');
        yield MenuItem::linkToCrud('Pages', 'fa fa-file', Page::class);
        yield MenuItem::linkToCrud('Sections', 'fa fa-layer-group', PageSection::class);
    }
}
