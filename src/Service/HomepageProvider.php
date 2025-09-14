<?php
declare(strict_types=1);

namespace App\Service;

use App\Service\Content\ConfigLoader;

final readonly class HomepageProvider
{
    public function __construct(private ConfigLoader $loader) {}

    private function getArray(string $key): array
    {
        $data = $this->loader->get($key);
        return \is_array($data) ? $data : [];
    }

    public function getViewModel(): array
    {
        // Sections classiques
        $site         = $this->getArray('site');
        $header       = $this->getArray('header');
        $hero         = $this->getArray('hero');
        $logoCloud    = $this->getArray('logo_cloud');
        $process      = $this->getArray('process');
        $services     = $this->getArray('services');
        $pricing      = $this->getArray('pricing');
        $cta          = $this->getArray('cta');
        $stats        = $this->getArray('stats');
        $testimonials = $this->getArray('testimonials');
        $footer       = $this->getArray('footer');
        $contact      = $this->getArray('contact');

        // Projets
        $projectsYaml = $this->loader->get('projects');
        $projects     = $projectsYaml['projects'] ?? [];

        // Map des sections à activer/désactiver
        $sections = $site['layout']['sections'] ?? [];

        return [
            'site'            => $site,
            'sections'        => $sections,
            'header'          => $header,
            'hero'            => $hero,
            'logo_cloud'      => $logoCloud,
            'process'         => $process,
            'services'        => $services,
            'pricing'         => $pricing,
            'cta'             => $cta,
            'stats'           => $stats,
            'testimonials'    => $testimonials,
            'footer'          => $footer,
            'contact'         => $contact,
            'projects'        => $projects,
            'selectedProject' => null, // toujours présent
        ];
    }
}
