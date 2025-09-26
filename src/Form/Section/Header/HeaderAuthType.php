<?php
declare(strict_types=1);

namespace App\Form\Section\Header;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class HeaderAuthType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('login_href', TextType::class, [
                'label' => 'Lien de connexion',
                'required' => false,
            ])
            ->add('login_label', TextType::class, [
                'label' => 'Libellé du bouton',
                'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null, // on mappe un array
        ]);
    }
}
