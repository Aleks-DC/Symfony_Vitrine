<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'Pricing', template: 'components/Pricing.html.twig')]
final class Pricing
{
    /** Entrée attendue: le tableau issu de pricing.yaml (via :tiers="pricing") */
    public array $tiers = [];

    // Variables exposées au template (tout est prêt, pas de calcul Twig nécessaire)
    public string $sectionId = 'tarifs';
    public string $headline = 'Pricing that grows with you';
    public ?string $subheadline = null;

    public string $currency = 'USD';
    public string $currencySymbol = '$';
    public string $labelMonthly = 'Monthly';
    public string $labelAnnually = 'Annually';
    public string $billedMonthly = 'Billed monthly';
    public string $billedAnnually = 'Billed annually';

    /** @var string[] ordre des colonnes (keys des tiers) */
    public array $cols = [];
    /** @var array<string,array> map key => tier */
    public array $tiersByKey = [];
    /** @var array[] comparaison des features */
    public array $groups = [];

    public function mount(?array $tiers = null, ?array $data = null): void
    {
        // On accepte :tiers="pricing" (ton appel actuel) ou :data="pricing"
        $src = $tiers ?? $data ?? [];

        // Section / titres
        $this->sectionId   = (string)($src['id'] ?? 'tarifs');
        $this->headline    = (string)($src['headline'] ?? $this->headline);
        $this->subheadline = isset($src['subheadline']) ? (string)$src['subheadline'] : null;

        // Fréquence / devise
        $freq = (array)($src['frequency'] ?? []);
        $this->currency       = (string)($freq['currency'] ?? 'USD');
        $this->labelMonthly   = (string)($freq['monthly_label'] ?? 'Monthly');
        $this->labelAnnually  = (string)($freq['annually_label'] ?? 'Annually');
        $billed                = (array)($freq['billed_text'] ?? []);
        $this->billedMonthly  = (string)($billed['monthly'] ?? 'Billed monthly');
        $this->billedAnnually = (string)($billed['annually'] ?? 'Billed annually');
        $this->currencySymbol = $this->symbolFor($this->currency);

        // Tiers + ordre des colonnes
        $tiers = (array)($src['tiers'] ?? []);
        $this->tiersByKey = [];
        foreach ($tiers as $t) {
            if (is_array($t) && isset($t['key'])) {
                $this->tiersByKey[(string)$t['key']] = $t;
            }
        }

        $order = (array)($src['comparison']['columns_order'] ?? []);
        if ($order) {
            $this->cols = array_values(array_filter(
                $order,
                fn($k) => is_string($k) && isset($this->tiersByKey[$k])
            ));
        } else {
            $this->cols = array_keys($this->tiersByKey);
        }

        // Groupes de comparaison
        $this->groups = (array)($src['comparison']['groups'] ?? []);
    }

    private function symbolFor(string $code): string
    {
        $map = [
            'USD' => '$', 'EUR' => '€', 'GBP' => '£',
            'CHF' => 'CHF', 'CAD' => '$', 'AUD' => '$',
            'JPY' => '¥', 'CNY' => '¥',
        ];
        return $map[$code] ?? '';
    }
}
