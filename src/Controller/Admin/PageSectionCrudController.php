<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Content\SectionType;
use App\Entity\PageSection;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Actions, Action, Crud, Filters};
use App\Form\Section\Header\HeaderPropsType;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\{AssociationField,
    BooleanField,
    ChoiceField,
    Field,
    IntegerField,
    TextareaField,
    TextField,
    FormField};

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
        return $filters->add('page')->add('type')->add('enabled');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->reorder(Crud::PAGE_INDEX, [Action::NEW, Action::EDIT, Action::DETAIL, Action::DELETE]);
    }

    public function configureFields(string $pageName): iterable
    {
        // commun
        yield AssociationField::new('page')->setRequired(true)->autocomplete();

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
            yield TextareaField::new('propsPretty', 'Props')->onlyOnDetail()->setFormTypeOption('disabled', true);
            return;
        }

        // ----- NEW / EDIT -----
        $typeField = ChoiceField::new('type', 'Type')->setChoices(
            array_combine(
                array_map(fn (SectionType $c) => $c->value, SectionType::cases()),
                SectionType::cases()
            )
        )->renderAsNativeWidget(false);

        // en édition, on fige le type pour éviter les incohérences de props
        if ($pageName === Crud::PAGE_EDIT) {
            $typeField->setFormTypeOption('disabled', true);
        }

        yield $typeField;

        yield BooleanField::new('enabled', 'Actif')->renderAsSwitch(true);
        yield IntegerField::new('position', 'Ordre');


        // ----- Zone Props : form typé si connu, sinon JSON -----
        /** @var PageSection|null $instance */
        $instance    = $this->getContext()?->getEntity()?->getInstance();
        $currentType = $instance?->getType();

        yield FormField::addPanel('Contenu de la section')->setIcon('fa fa-sliders');

        $instance    = $this->getContext()?->getEntity()?->getInstance();
        $currentType = $instance?->getType();

        if ($currentType === SectionType::header) {
            yield Field::new('propsForm', 'Props')
                ->onlyOnForms()
                ->setFormType(HeaderPropsType::class)
                ->setFormTypeOptions([
                    'data_class' => null,
                    'empty_data' => [],
                ]);
            return;
        }


// fallback JSON si autre type
        yield TextareaField::new('propsJson', 'Props (JSON)')
            ->setNumOfRows(18)
            ->setHelp('Choisis/Enregistre le type pour avoir un formulaire dédié.');
}}
