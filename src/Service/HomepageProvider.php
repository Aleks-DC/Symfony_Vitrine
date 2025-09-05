<?php
declare(strict_types=1);

namespace App\Service;

use App\Service\Content\ConfigLoader;

final readonly class HomepageProvider
{
    public function __construct(private ConfigLoader $loader) {}

    public function getViewModel(): array
    {
        // Sections classiques (data -> array)
        $site          = $this->loader->get('site');
        $header        = $this->loader->get('header');
        $hero          = $this->loader->get('hero');
        $logoCloud     = $this->loader->get('logo_cloud');
        $process       = $this->loader->get('process');
        $services      = $this->loader->get('services');
        $pricing       = $this->loader->get('pricing');
        $cta           = $this->loader->get('cta');
        $stats         = $this->loader->get('stats');
        $testimonials  = $this->loader->get('testimonials');
        $footer        = $this->loader->get('footer');
        $contact       = $this->loader->get('contact');

        // Projets
        $projectsYaml  = $this->loader->get('projects');
        $projects      = $projectsYaml['projects'] ?? [];

        return [
            'site'          => $site,
            'header'        => $header,
            'hero'          => $hero,
            'logo_cloud'    => $logoCloud,
            'process'       => $process,
            'services'      => $services,
            'pricing'       => $pricing,
            'cta'           => $cta,
            'stats'         => $stats,
            'testimonials'  => $testimonials,
            'footer'        => $footer,
            'contact'       => $contact,
            'projects'      => $projects,
        ];
    }
}
