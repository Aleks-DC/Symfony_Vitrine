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
            ->add('label', TextType::class, ['label' => 'Label', 'required' => true])
            ->add('href',  TextType::class, ['label' => 'Lien (ex: #services)', 'required' => true]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // chaque item est un array ['label'=>..., 'href'=>...]
            'empty_data' => function () { return ['label' => '', 'href' => '']; },
        ]);
    }
}
