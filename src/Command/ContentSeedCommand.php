<?php
declare(strict_types=1);


namespace App\Command;


use App\Content\SectionType;
use App\Entity\Page;
use App\Entity\PageSection;
use App\Service\Content\ConfigLoader;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


#[AsCommand(name: 'app:content:seed')]
class ContentSeedCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly ConfigLoader $loader,
    ) { parent::__construct(); }


    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $site = $this->loader->get('site');
        $gates = (array)($site['layout']['sections'] ?? []);


        $page = new Page();
// slug/title par défaut
        $ref = new \ReflectionProperty($page, 'slug'); $ref->setAccessible(true); $ref->setValue($page, 'home');
        $ref = new \ReflectionProperty($page, 'title'); $ref->setAccessible(true); $ref->setValue($page, 'Accueil');
        $this->em->persist($page);


        $defaultOrder = [
            SectionType::header,
            SectionType::hero,
            SectionType::logo_cloud,
            SectionType::process,
            SectionType::services,
            SectionType::pricing,
            SectionType::cta,
            SectionType::stats,
            SectionType::testimonials,
            SectionType::projects,
            SectionType::contact,
            SectionType::footer,
        ];


        $pos = 1;
        foreach ($defaultOrder as $type) {
            $ps = new PageSection();
            $ps->setPage($page);
            $ps->setType($type);
            $ps->setEnabled((bool)($gates[$type->value] ?? true));
            $ps->setPosition($pos++);
// props = données YAML spécifiques à la section si dispo
            $ps->setProps($this->loader->get($type->value));
            $this->em->persist($ps);
        }


        $this->em->flush();
        $output->writeln('<info>Seed OK</info>');
        return Command::SUCCESS;
    }
}
