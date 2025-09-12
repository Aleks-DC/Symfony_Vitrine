<?php
declare(strict_types=1);

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'ContactForm', template: 'components/ContactForm.html.twig')]
final class ContactForm
{
    // Section
    public string $sectionId = 'contact';

    // Image
    public ?string $imageUrl = null;
    public ?string $imageAlt = null;

    // Titres
    public string $title = "Let's work together";
    public ?string $subtitle = null;

    // Form meta
    public string $action = '#';
    public string $method = 'POST';
    public string $buttonLabel = 'Send message';

    // Labels
    public string $labelFirstName = 'First name';
    public string $labelLastName  = 'Last name';
    public string $labelEmail     = 'Email';
    public string $labelCompany   = 'Company';
    public string $labelPhone     = 'Phone';
    public string $labelMessage   = 'How can we help you?';
    public string $labelBudget    = 'Expected budget';

    // Aides
    public ?string $helpPhone = null;
    public ?string $helpMessage = null;

    /** @var array<int,array{ id:string, value:string, label:string, checked?:bool }> */
    public array $budgetOptions = [];

    /** Pré-remplissage du form (POST précédent) */
    public array $values = [];

    /** Erreurs par champ (POST précédent) */
    public array $errors = [];

    /** Flag succès pour afficher l’alerte verte */
    public bool $contactSuccess = false;

    public function mount(?array $data = null, ?array $form = null): void
    {
        // On accepte :data="contact" OU :form="contact" (même souplesse que Pricing)
        $src = $data ?? $form ?? [];

        // Section
        $this->sectionId = (string)($src['id'] ?? 'contact');

        // Image
        $img = (array)($src['image'] ?? []);
        $this->imageUrl = isset($img['src'])
            ? (string)$img['src']
            : (isset($img['url']) ? (string)$img['url'] : null); // fallback compat
        $this->imageAlt = isset($img['alt']) ? (string)$img['alt'] : null;

        // Titres
        $this->title    = (string)($src['title'] ?? $this->title);
        $this->subtitle = isset($src['subtitle']) ? (string)$src['subtitle'] : null;

        // Form meta
        $formMeta = (array)($src['form'] ?? []);
        $this->action      = (string)($formMeta['action'] ?? $this->action);
        $this->method      = (string)($formMeta['method'] ?? $this->method);
        $this->buttonLabel = (string)($formMeta['button_label'] ?? $this->buttonLabel);

        // Labels
        $labels = (array)($formMeta['labels'] ?? []);
        $this->labelFirstName = (string)($labels['first_name'] ?? $this->labelFirstName);
        $this->labelLastName  = (string)($labels['last_name']  ?? $this->labelLastName);
        $this->labelEmail     = (string)($labels['email']      ?? $this->labelEmail);
        $this->labelCompany   = (string)($labels['company']    ?? $this->labelCompany);
        $this->labelPhone     = (string)($labels['phone']      ?? $this->labelPhone);
        $this->labelMessage   = (string)($labels['message']    ?? $this->labelMessage);
        $this->labelBudget    = (string)($labels['budget']     ?? $this->labelBudget);

        // Aides
        $help = (array)($formMeta['help'] ?? []);
        $this->helpPhone   = isset($help['phone'])   ? (string)$help['phone']   : null;
        $this->helpMessage = isset($help['message']) ? (string)$help['message'] : null;

        // Budget
        $opts = (array)($formMeta['budget_options'] ?? []);
        $this->budgetOptions = [];
        foreach ($opts as $o) {
            if (!is_array($o)) continue;
            $id     = (string)($o['id'] ?? '');
            $value  = (string)($o['value'] ?? '');
            $label  = (string)($o['label'] ?? ($value !== '' ? ucfirst($value) : ''));
            if ($id === '' || $value === '' || $label === '') continue;

            $this->budgetOptions[] = [
                'id'      => $id,
                'value'   => $value,
                'label'   => $label,
                'checked' => (bool)($o['checked'] ?? false),
            ];
        }
    }
}
