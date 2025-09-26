<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Page;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Actions, Action, Crud};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField, IdField, SlugField, TextField};

final class PageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Page::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Page')
            ->setEntityLabelInPlural('Pages')
            ->setDefaultSort(['title' => 'ASC'])
            ->setSearchFields(['title', 'slug'])
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        // Ajoute le bouton "Detail" sur l'index
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')->onlyOnIndex();

        yield TextField::new('title', 'Titre')
            ->setHelp('Nom lisible côté admin.');

        // Slug auto depuis "title" lors de la création. On le laisse éditable si besoin.
        $slug = SlugField::new('slug')
            ->setTargetFieldName('title')
            ->setHelp('Identifiant unique de la page (ex: home, services…).');
        yield $slug;

        // Affiche les sections liées sur la page de détail (lecture seule)
        yield AssociationField::new('sections', 'Sections')
            ->onlyOnDetail()
            ->setHelp('Les sections se gèrent dans le menu “Sections”.');
    }
}
