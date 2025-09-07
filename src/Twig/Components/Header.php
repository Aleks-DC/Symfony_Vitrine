<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Service\Content\ConfigLoader;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'Header')]
final class Header
{
    public function __construct(private readonly ConfigLoader $loader) {}

    /** Données exposées au template (déjà normalisées) */
    public array $brand = [];
    public array $menu = [];
    public array $auth = [];
    public array $mobile = [];

    public function mount(): void
    {
        // Charge content/header.yaml avec des valeurs par défaut sûres
        $data = $this->loader->get('header');

        $this->brand = [
            'name' => $data['brand']['name'] ?? 'Your Company',
            'logo' => [
                'src' => $data['brand']['logo']['src'] ?? 'https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500',
                'alt' => $data['brand']['logo']['alt'] ?? 'Your Company',
            ],
        ];

        $this->menu  = \is_array($data['menu'] ?? null) ? $data['menu'] : [];
        $this->auth  = [
            'login_label' => $data['auth']['login_label'] ?? 'Log in',
            'login_href'  => $data['auth']['login_href']  ?? '#',
        ];
        $this->mobile = [
            'dialog_id' => $data['mobile']['dialog_id'] ?? 'mobile-menu',
        ];
    }
}
