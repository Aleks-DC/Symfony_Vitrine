<?php
declare(strict_types=1);

namespace App\Entity;

use App\Repository\PageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\Table(
    name: 'page',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'uniq_page_slug', columns: ['slug'])
    ]
)]
class Page
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // slug unique pour identifier la page (home, services, …)
    #[ORM\Column(length: 64)]
    private ?string $slug = null;

    #[ORM\Column(length: 128)]
    private ?string $title = null;

    /** @var Collection<int, PageSection> */
    #[ORM\OneToMany(
        targetEntity: PageSection::class,
        mappedBy: 'page',
        cascade: ['persist', 'remove'],
        orphanRemoval: true
    )]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $sections;

    public function __construct()
    {
        $this->sections = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;
        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    /** @return Collection<int, PageSection> */
    public function getSections(): Collection
    {
        return $this->sections;
    }

    public function addSection(PageSection $section): self
    {
        if (!$this->sections->contains($section)) {
            $this->sections->add($section);
            $section->setPage($this);
        }
        return $this;
    }

    public function removeSection(PageSection $section): self
    {
        if ($this->sections->removeElement($section)) {
            if ($section->getPage() === $this) {
                $section->setPage(null);
            }
        }
        return $this;
    }
    public function __toString(): string
    {
        return $this->title
            ?? $this->slug
            ?? ('Page #' . ($this->id ?? ''));
    }
}
