<?php
declare(strict_types=1);

namespace App\Service\Content;

use Symfony\Component\Yaml\Yaml;

final readonly class ConfigLoader
{
    public function __construct(private string $contentDir) {}

    /**
     * Load a YAML file from content/<name>.yaml and return it as array.
     * Returns [] if the file is missing or invalid.
     */
    public function get(string $name): array
    {
        $file = rtrim($this->contentDir, "/\\") . DIRECTORY_SEPARATOR . $name . '.yaml';

        if (!is_file($file)) {
            return []; // safe default
        }

        $data = Yaml::parseFile($file);

        return is_array($data) ? $data : [];
    }
}
