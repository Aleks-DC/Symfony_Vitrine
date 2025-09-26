<?php
declare(strict_types=1);

namespace App\Entity;

use App\Content\SectionType;
use App\Repository\PageSectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageSectionRepository::class)]
#[ORM\Table(
    name: 'page_section',
    indexes: [
        new ORM\Index(name: 'idx_page_section_position', columns: ['position'])
    ],
    uniqueConstraints: [
        // Empêche d’avoir deux fois le même type de section sur la même page
        new ORM\UniqueConstraint(name: 'uniq_page_type', columns: ['page_id', 'type'])
    ]
)]
class PageSection
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sections')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Page $page = null;

    // Enum stocké par Doctrine (valeur string en base)
    #[ORM\Column(enumType: SectionType::class)]
    private SectionType $type;

    #[ORM\Column(options: ['default' => true])]
    private bool $enabled = true;

    #[ORM\Column(options: ['default' => 0])]
    private int $position = 0;

    #[ORM\Column(type: 'json')]
    private array $props = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPage(): ?Page
    {
        return $this->page;
    }

    public function setPage(?Page $page): self
    {
        $this->page = $page;
        return $this;
    }

    public function getType(): SectionType
    {
        return $this->type;
    }

    public function setType(SectionType $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;
        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;
        return $this;
    }

    public function getProps(): array
    {
        return $this->props;
    }

    public function setProps(array $props): self
    {
        $this->props = $props;
        return $this;
    }

    public function getPropsPreview(): string
    {
        if (empty($this->props)) {
            return '—';
        }
        $json = json_encode($this->props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return mb_strlen($json) > 120 ? mb_substr($json, 0, 120) . '…' : $json;
    }

    public function getPropsPretty(): string
    {
        return json_encode($this->props, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
    }

    public function getTypeLabel(): string
    {
        return $this->type->value; // SectionType est un BackedEnum<string>
    }

    public function getPropsJson(): string
    {
        // JSON joli pour l'édition
        return $this->props === [] ? '' :
            (json_encode($this->props, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '');
    }

    public function setPropsJson(?string $json): self
    {
        $json = trim((string) $json);
        if ($json === '') {
            $this->props = [];
            return $this;
        }

        $data = json_decode($json, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
            $this->props = $data;
        }
        // (optionnel) else: conserver l'ancien contenu ou lever une exception/validation

        return $this;
    }



}
