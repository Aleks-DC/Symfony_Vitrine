<?php
declare(strict_types=1);

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

final class ContactFormData
{
    public function __construct(
        #[Assert\NotBlank(message: 'Prénom requis.')]
        #[Assert\Length(max: 80, maxMessage: '80 caractères max.')]
        public string $first_name,

        #[Assert\NotBlank(message: 'Nom requis.')]
        #[Assert\Length(max: 80, maxMessage: '80 caractères max.')]
        public string $last_name,

        #[Assert\NotBlank(message: 'Email requis.')]
        #[Assert\Email(message: 'Email invalide.')]
        #[Assert\Length(max: 180, maxMessage: '180 caractères max.')]
        public string $email,

        #[Assert\Length(max: 120, maxMessage: '120 caractères max.')]
        public ?string $company,

        #[Assert\Length(max: 30, maxMessage: '30 caractères max.')]
        #[Assert\Regex(pattern: '/^[0-9+().\s-]{6,}$/', message: 'Numéro invalide.')]
        public ?string $phone,

        #[Assert\NotBlank(message: 'Message requis.')]
        #[Assert\Length(max: 500, maxMessage: '500 caractères max.')]
        public string $message,

        #[Assert\NotBlank(message: 'Choisissez un budget.')]
        public string $budget,
    ) {}
}
