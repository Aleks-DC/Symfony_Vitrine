<?php
declare(strict_types=1);

namespace App\Service\Content;

use Symfony\Component\Yaml\Yaml;

final readonly class ConfigLoader
{
    public function __construct(private string $contentDir) {}

    /**
     * @param string $filename
     * @return array
     */
    public function load(string $filename): array
    {
        return Yaml::parseFile($this->contentDir.'/'.$filename);
    }
}
