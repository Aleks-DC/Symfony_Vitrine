<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Content\SectionType;
use App\Entity\PageSection;
use EasyCorp\Bundle\EasyAdminBundle\Config\{Actions, Action, Crud, Filters};
use App\Form\Section\Header\HeaderMenuItemType;
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
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

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

        if ($currentType === \App\Content\SectionType::header) {
            // Champ virtuel "menu" mappé sur props[menu] (et PAS props tout court)
            yield \EasyCorp\Bundle\EasyAdminBundle\Field\Field::new('menu', 'Menu')
                ->onlyOnForms()
                ->setFormType(CollectionType::class)
                ->setFormTypeOptions([
                    'property_path' => 'props[menu]',      // <-- clé du JSON ciblée
                    'entry_type'    => HeaderMenuItemType::class,
                    'allow_add'     => true,
                    'allow_delete'  => true,
                    'delete_empty'  => true,
                    'by_reference'  => false,
                ]);

            return; // pas de fallback JSON ici
        }

    }}
