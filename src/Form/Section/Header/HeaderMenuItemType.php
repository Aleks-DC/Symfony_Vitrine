<?php
declare(strict_types=1);

namespace App\Form\Section\Header;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HeaderMenuItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'label' => 'Label',
                'required' => true,
            ])
            ->add('href', TextType::class, [
                'label' => 'Cible (ancre SPA)',
                'required' => true,
                'help' => 'Ex: #services, #methode, #tarifs…',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        // chaque item est un ARRAY
        $resolver->setDefaults([
            'data_class' => null,
            'empty_data' => [],
        ]);
    }
}
