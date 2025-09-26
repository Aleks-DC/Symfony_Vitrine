<?php
declare(strict_types=1);


namespace App\Service\Content;


use App\Content\SectionType;
use App\Entity\PageSection;
use App\Repository\PageRepository;
use Psr\Log\LoggerInterface;


final readonly class ContentResolver
{
    public function __construct(
        private PageRepository $pages,
        private ConfigLoader $loader,
        private LoggerInterface $logger,
    ) {}


    /**
     * Retourne [ ['type' => 'hero', 'enabled' => true, 'data' => array], ... ]
     */
    public function componentsFor(string $slug = 'home'): array
    {
        try {
            if ($page = $this->pages->findOneBy(['slug' => $slug])) {
                $out = [];
                /** @var PageSection $ps */
                foreach ($page->getSections() as $ps) {
                    $data = $ps->getProps();
                    if (!$data) {
// fallback per-section si props vides
                        $data = $this->loader->get($ps->getType()->value);
                    }
                    $out[] = [
                        'type' => $ps->getType()->value,
                        'enabled' => $ps->isEnabled(),
                        'data' => $data,
                    ];
                }
                return $out;
            }
        } catch (\Throwable $e) {
            $this->logger->error('DB indisponible : fallback YAML', ['exception' => $e]);
        }


// Fallback complet YAML
        $out = [];
        foreach (SectionType::cases() as $type) {
            $site = $this->loader->get('site');
            $gates = (array)($site['layout']['sections'] ?? []);
            $out[] = [
                'type' => $type->value,
                'enabled' => (bool)($gates[$type->value] ?? true),
                'data' => $this->loader->get($type->value),
            ];
        }
        return $out;
    }
}
