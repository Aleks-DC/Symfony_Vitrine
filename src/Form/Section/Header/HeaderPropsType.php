<?php
declare(strict_types=1);

namespace App\Form\Section\Header;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HeaderPropsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $b, array $o): void
    {
        $b->add('menu', CollectionType::class, [
            'label'        => 'Menu',
            'required'     => false,
            'entry_type'   => HeaderMenuItemType::class, // <- le sous-formulaire ci-dessous
            'allow_add'    => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'by_reference' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $r): void
    {
        $r->setDefaults([
            'data_class' => null, // on édite un array (celui de "props")
            'empty_data' => [],
        ]);
    }
}
