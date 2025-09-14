<?php
declare(strict_types=1);

namespace App\Twig;

use App\Service\Content\LayoutGates;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class LayoutExtension extends AbstractExtension
{
    public function __construct(private readonly LayoutGates $gates) {}

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_enabled', [$this->gates, 'isEnabled']),
            new TwigFunction('layout_sections', [$this->gates, 'all']),
        ];
    }
}
