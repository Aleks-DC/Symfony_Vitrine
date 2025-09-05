<?php
declare(strict_types=1);

namespace App\Service;

use App\Service\Content\ConfigLoader;

final readonly class HomepageProvider
{
    public function __construct(private ConfigLoader $loader) {}

    /**
     * @return array{
     *  site:array,
     *  stats:list<array{label:string,value:int|string}>,
     *  projects:list<array<string,mixed>>,
     *  testimonials:list<array{author:string,quote:string}>,
     *  pricing:list<array<string,mixed>>,
     *  process:list<array{title:string,text:string}>
     * }
     */
    public function get(): array
    {
        return [
            'site'         => $this->safe('site.yaml'),
            'stats'        => $this->safe('stats.yaml'),
            'projects'     => $this->safe('projects.yaml'),
            'testimonials' => $this->safe('testimonials.yaml'),
            'pricing'      => $this->safe('pricing.yaml'),
            'process'      => $this->safe('process.yaml'),
        ];
    }

    /**
     * @param string $file
     * @return array
     */
    private function safe(string $file): array
    {
        try {
            $data = $this->loader->load($file);
            return \is_array($data) ? $data : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
