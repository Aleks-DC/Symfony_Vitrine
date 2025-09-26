<?php
declare(strict_types=1);

namespace App\Form\Section\Header;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HeaderPropsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // -------- brand --------
        $builder->add('brand', FormType::class, [
            'label' => 'Brand',
            'required' => false,
            'data_class' => null,
        ]);
        $builder->get('brand')
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'required' => false,
            ])
            ->add('logo', FormType::class, [
                'label' => 'Logo',
                'required' => false,
                'data_class' => null,
            ]);
        $builder->get('brand')->get('logo')
            ->add('src', TextType::class, [
                'label' => 'URL du logo',
                'required' => false,
            ])
            ->add('alt', TextType::class, [
                'label' => 'Texte alternatif',
                'required' => false,
            ]);

        // -------- menu (array d’items) --------
        $builder->add('menu', CollectionType::class, [
            'label' => 'Menu',
            'required' => false,
            'entry_type' => HeaderMenuItemType::class,
            'allow_add' => true,
            'allow_delete' => true,
            'delete_empty' => true,
            'by_reference' => false,
        ]);

        // -------- auth --------
        $builder->add('auth', FormType::class, [
            'label' => 'Authentification',
            'required' => false,
            'data_class' => null,
        ]);
        $builder->get('auth')
            ->add('login_label', TextType::class, [
                'label' => 'Label du bouton',
                'required' => false,
            ])
            ->add('login_href', TextType::class, [
                'label' => 'Lien (ancre SPA)',
                'required' => false,
                'help' => 'Ex: #contact',
            ]);

        // -------- mobile --------
        $builder->add('mobile', FormType::class, [
            'label' => 'Mobile',
            'required' => false,
            'data_class' => null,
        ]);
        $builder->get('mobile')
            ->add('dialog_id', TextType::class, [
                'label' => 'ID du dialog',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // le root édite l’ARRAY complet "props"
        $resolver->setDefaults([
            'data_class' => null,
            'empty_data' => [],
        ]);
    }
}
