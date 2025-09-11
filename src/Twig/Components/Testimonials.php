<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Testimonials')]
final class Testimonials
{
    /** On peut passer soit la liste de quotes, soit l'objet YAML entier */
    public array $quotes = [];

    /** En-tête (surchargés si l'objet YAML les fournit) */
    public string $id = 'avis';
    public string $eyebrow = 'Testimonials';
    public string $title = 'We have worked with thousands of amazing people';
    public ?string $subtitle = null;

    /** Layout: 'fixed' (pattern 3/2/2/3) ou 'auto' (répartition dynamique) */
    public string $layout = 'fixed';

    /** Pré-calculs */
    public ?array $featured = null;
    public array $others = [];

    /** Groupes prêts pour le template (toujours présents) */
    public array $groups = [
        'left_top'    => [],
        'left_bottom' => [],
        'right_top'   => [],
        'right_bottom'=> [],
    ];

    public function mount(array $quotes = [], string $layout = 'fixed'): void
    {
        $this->quotes = $quotes;
        $this->layout = $layout;

        // Si on a reçu l'objet YAML complet, on "déplie"
        if ($this->isAssoc($this->quotes) && (
                isset($this->quotes['quotes']) || isset($this->quotes['title']) || isset($this->quotes['eyebrow'])
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

        // Construit les groupes pour la grille
        $this->buildGroups($this->others);
    }

    private function buildGroups(array $rest): void
    {
        $n = count($rest);

        if ($this->layout === 'fixed') {
            // Pattern Tailwind UI d'origine pour 10 items: 3/2 (gauche), 2/3 (droite)
            $this->groups['left_top']    = array_slice($rest, 0, 3);
            $this->groups['left_bottom'] = array_slice($rest, 3, 2);
            $this->groups['right_top']   = array_slice($rest, 5, 2);
            $this->groups['right_bottom']= array_slice($rest, 7, 3);
            return;
        }

        // --- AUTO: répartition proportionnelle, indépendante du nombre d'items ---
        // On conserve la "tendance" visuelle du pattern (60/40 & 40/60)
        $leftCount  = (int)ceil($n / 2);      // moitié gauche
        $rightCount = $n - $leftCount;        // moitié droite

        [$lt, $lb] = $this->splitCounts($leftCount, 0.60);  // 60% haut / 40% bas
        [$rt, $rb] = $this->splitCounts($rightCount, 0.40); // 40% haut / 60% bas

        $i = 0;
        $this->groups['left_top']     = array_slice($rest, $i, $lt); $i += $lt;
        $this->groups['left_bottom']  = array_slice($rest, $i, $lb); $i += $lb;
        $this->groups['right_top']    = array_slice($rest, $i, $rt); $i += $rt;
        $this->groups['right_bottom'] = array_slice($rest, $i, $rb); $i += $rb;

        // S'il reste 1 élément à cause des arrondis, on le met à droite bas
        if ($i < $n) {
            $this->groups['right_bottom'] = array_merge(
                $this->groups['right_bottom'],
                array_slice($rest, $i)
            );
        }
    }

    private function splitCounts(int $total, float $topRatio): array
    {
        if ($total <= 0) return [0, 0];
        $top = (int)round($total * $topRatio);
        if ($top > $total) $top = $total;
        if ($top < 0) $top = 0;
        $bottom = $total - $top;
        return [$top, $bottom];
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

        // logo string ou {light,dark} -> un seul logo (pas de dark/light côté rendu)
        $logo = $q['logo'] ?? null;
        if (is_array($logo)) {
            $logo = $logo['light'] ?? ($logo['dark'] ?? null);
        }
        $q['logo'] = is_string($logo) ? $logo : null;

        $q['featured'] = (bool)($q['featured'] ?? false);

        return $q;
    }
}
