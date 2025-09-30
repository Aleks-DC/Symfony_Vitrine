<?php
declare(strict_types=1);

namespace App\Twig\Components;

use App\Service\Content\ConfigLoader;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'Header')]
final class Header
{
    public function __construct(private readonly ConfigLoader $loader) {}

    public array $brand = [];
    public array $menu = [];
    public array $auth = [];
    public array $mobile = [];

    public function mount(
        array $brand = [],
        array $menu = [],
        array $auth = [],
        array $mobile = []
    ): void {
        $defaults = (array) ($this->loader->get('header') ?? []);

        // fusion profonde pour objets
        $this->brand  = array_replace_recursive((array)($defaults['brand']  ?? []), $brand);
        $this->auth   = array_replace_recursive((array)($defaults['auth']   ?? []), $auth);
        $this->mobile = array_replace_recursive((array)($defaults['mobile'] ?? []), $mobile);

        // listes: si la BDD fournit quelque chose, on l'utilise tel quel; sinon YAML
        $this->menu = $menu !== [] ? $menu : (array)($defaults['menu'] ?? []);
    }
}
