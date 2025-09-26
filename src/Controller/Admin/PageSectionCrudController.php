<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Content\SectionType;
use App\Entity\PageSection;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Actions, Action, Crud, Filters};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField,
    BooleanField,
    ChoiceField,
    IntegerField,
    TextareaField,
    TextField};
use Symfony\Component\Validator\Constraints as Assert;


final class PageSectionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PageSection::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Section de page')
            ->setEntityLabelInPlural('Sections de page')
            ->setDefaultSort(['page' => 'ASC', 'position' => 'ASC'])
            ->showEntityActionsInlined()
            ->setSearchFields(['page.slug', 'page.title', 'type'])
            ->setPaginatorPageSize(50);
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('page')
            ->add('type')
            ->add('enabled');
    }

    public function configureActions(Actions $actions): Actions
    {
        // Actions index: NEW, EDIT, DELETE + DUPLICATE (optionnel)
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::NEW, Action::EDIT, Action::DETAIL, Action::DELETE]);
    }

    public function configureFields(string $pageName): iterable
    {
        // commun
        yield AssociationField::new('page')
            ->setRequired(true)
            ->autocomplete();

        if ($pageName === Crud::PAGE_INDEX) {
            yield TextField::new('typeLabel', 'Type');
            yield BooleanField::new('enabled', 'Actif')->renderAsSwitch(false);
            yield IntegerField::new('position', 'Ordre');
            yield TextField::new('propsPreview', 'Props');
            return;
        }

        if ($pageName === Crud::PAGE_DETAIL) {
            yield TextField::new('typeLabel', 'Type');
            yield BooleanField::new('enabled', 'Actif');
            yield IntegerField::new('position', 'Ordre');
            yield TextareaField::new('propsPretty', 'Props')
                ->onlyOnDetail()
                ->setFormTypeOption('disabled', true);
            return;
        }

        // ----- FORM NEW/EDIT -----
        yield ChoiceField::new('type', 'Type')->setChoices(
            array_combine(
                array_map(fn (SectionType $c) => $c->value, SectionType::cases()),
                SectionType::cases()
            )
        )->renderAsNativeWidget(false);

        yield BooleanField::new('enabled', 'Actif')->renderAsSwitch(true);
        yield IntegerField::new('position', 'Ordre');

        // ⚠️ ici on utilise la string virtuelle propsJson
        yield TextareaField::new('propsJson', 'Props (JSON)')
            ->setNumOfRows(18)
            ->setHelp('Colle un objet/array JSON. Laisse vide pour []')
            ->setFormTypeOptions([
                'empty_data'  => '',                            // évite null
                'constraints' => [new Assert\Json(message: 'JSON invalide')], // feedback propre
            ]);
    }}
