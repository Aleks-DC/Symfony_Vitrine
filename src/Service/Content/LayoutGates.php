<?php
declare(strict_types=1);

namespace App\Service\Content;

final class LayoutGates
{
    /** @var array<string,bool|int|string> */
    private array $sections;

    public function __construct(private readonly ConfigLoader $loader)
    {
        $site = $this->loader->get('site');
        $this->sections = (array)($site['layout']['sections'] ?? []);
    }

    /**
     * True si la section est activée.
     * Si la clé n’existe pas dans le YAML → fallback à true.
     */
    public function isEnabled(string $name): bool
    {
        return \array_key_exists($name, $this->sections)
            ? (bool)$this->sections[$name]
            : true;
    }

    /**
     * Map brute pour debug/BO.
     * @return array<string,bool|int|string>
     */
    public function all(): array
    {
        return $this->sections;
    }
}
