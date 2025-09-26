<?php
declare(strict_types=1);

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Exception\TransformationFailedException;

final class JsonTextareaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addModelTransformer(new CallbackTransformer(
        // array -> string (affichage)
            function (?array $value): string {
                return $value === null ? '' : json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            },
            // string -> array (soumission)
            function (?string $submitted): array {
                $submitted = trim($submitted ?? '');
                if ($submitted === '') {
                    return [];
                }
                $data = json_decode($submitted, true, 512, JSON_THROW_ON_ERROR);
                if (!is_array($data)) {
                    throw new TransformationFailedException('Le JSON doit représenter un objet/array.');
                }
                return $data;
            }
        ));
    }

    public function getParent(): string
    {
        return TextareaType::class;
    }
}
