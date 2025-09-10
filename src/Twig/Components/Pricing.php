<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Pricing')]
final class Pricing
{
    /** Props pouvant être passées depuis le template */
    public array $pricing = []; // recommandé
    public array $data = [];    // compat
    public array $tiers = [];   // compat (si on passait juste les tiers)

    /** Valeurs préparées pour Twig (read-only côté template) */
    public string $sectionId = 'tarifs';
    public string $headline = 'Pricing that grows with you';
    public string $subheadline = "Choose an affordable plan that’s packed with the best features for engaging your audience, creating customer loyalty, and driving sales.";

    public string $currency = 'USD';
    public string $currencySymbol = '$';
    public string $labelMonthly = 'Monthly';
    public string $labelAnnually = 'Annually';
    public string $billedMonthly = 'Billed monthly';
    public string $billedAnnually = 'Billed annually';

    /** Données structurées pour l’affichage */
    public array $tiersPrepared = []; // liste des tiers
    public array $cols = [];          // ordre des colonnes pour la comparaison
    public array $tiersByKey = [];    // index tiers[key] => tier
    public array $groups = [];        // groupes de comparaison

    public function mount(): void
    {
        // 1) Source unique
        $src = $this->pricing ?: ($this->data ?: (empty($this->tiers) ? [] : ['tiers' => $this->tiers]));

        // 2) Defaults simples
        $this->sectionId   = (string)($src['id'] ?? $this->sectionId);
        $this->headline    = (string)($src['headline'] ?? $this->headline);
        $this->subheadline = (string)($src['subheadline'] ?? $this->subheadline);

        $freq = $src['frequency'] ?? [];
        $this->currency       = (string)($freq['currency'] ?? $this->currency);
        $this->currencySymbol = $this->currency === 'USD' ? '$' : '';
        $this->labelMonthly   = (string)($freq['monthly_label'] ?? $this->labelMonthly);
        $this->labelAnnually  = (string)($freq['annually_label'] ?? $this->labelAnnually);

        $billed = $freq['billed_text'] ?? [];
        $this->billedMonthly  = (string)($billed['monthly'] ?? $this->billedMonthly);
        $this->billedAnnually = (string)($billed['annually'] ?? $this->billedAnnually);

        // 3) Tiers + index
        $tiers = \is_array($src['tiers'] ?? null) ? $src['tiers'] : [];
        $this->tiersPrepared = $tiers;

        $this->tiersByKey = [];
        foreach ($tiers as $t) {
            if (\is_array($t) && isset($t['key'])) {
                $this->tiersByKey[(string)$t['key']] = $t;
            }
        }

        // 4) Colonnes de comparaison
        $comp = \is_array($src['comparison'] ?? null) ? $src['comparison'] : [];
        $order = $comp['columns_order'] ?? null;
        if (\is_array($order) && $order) {
            $this->cols = $order;
        } else {
            // fallback: ordre des tiers
            $this->cols = array_values(array_keys($this->tiersByKey));
        }

        // 5) Groupes (tableau ou [])
        $this->groups = \is_array($comp['groups'] ?? null) ? $comp['groups'] : [];
    }
}
