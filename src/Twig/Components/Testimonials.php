<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Testimonials')]
final class Testimonials
{
    /** You pass either the quotes list OR the whole testimonials YAML object */
    public array $quotes = [];

    // Header defaults (overridden if YAML object provides them)
    public string $id = 'avis';
    public string $eyebrow = 'Testimonials';
    public string $title = 'We have worked with thousands of amazing people';
    public ?string $subtitle = null;

    // Precomputed for Twig (no method calls in template)
    public ?array $featured = null;
    public array $others = [];

    public function mount(array $quotes = []): void
    {
        // Utiliser la prop passée au composant
        $this->quotes = $quotes;

        // Si on a reçu l'objet YAML complet, on "déplie"
        if ($this->isAssoc($this->quotes) && (
                isset($this->quotes['quotes']) ||
                isset($this->quotes['title']) ||
                isset($this->quotes['eyebrow'])
            )) {
            $data = $this->quotes;
            $this->id       = $data['id']       ?? $this->id;
            $this->eyebrow  = $data['eyebrow']  ?? $this->eyebrow;
            $this->title    = $data['title']    ?? $this->title;
            $this->subtitle = $data['subtitle'] ?? $this->subtitle;
            $this->quotes   = is_array($data['quotes'] ?? null) ? $data['quotes'] : [];
        }

        // Normalise tout
        $normalized = array_map([$this, 'normalize'], $this->quotes);

        // Featured = 1ère avec featured=true, sinon 1ère
        foreach ($normalized as $q) {
            if (!empty($q['featured'])) { $this->featured = $q; break; }
        }
        $this->featured ??= ($normalized[0] ?? null);

        // Les autres
        $this->others = $this->featured
            ? array_values(array_filter($normalized, fn ($q) => $q !== $this->featured))
            : [];

        // Conserver la version normalisée (optionnel)
        $this->quotes = $normalized;
    }

    private function isAssoc(array $arr): bool
    {
        if ($arr === []) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    private function normalize(array $q): array
    {
        $q['text'] = $q['text'] ?? '';

        $a = $q['author'] ?? [];
        $q['author'] = [
            'name'   => $a['name']   ?? '',
            'handle' => $a['handle'] ?? null,
            'avatar' => $a['avatar'] ?? null,
        ];

        // Accept string logo OR {light,dark} — we keep a single logo (no dark/light mode)
        $logo = $q['logo'] ?? null;
        if (is_array($logo)) {
            $logo = $logo['light'] ?? ($logo['dark'] ?? null);
        }
        $q['logo'] = is_string($logo) ? $logo : null;

        $q['featured'] = (bool)($q['featured'] ?? false);

        return $q;
    }
}
