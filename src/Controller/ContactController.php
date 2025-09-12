<?php
declare(strict_types=1);

namespace App\Controller;

use App\Dto\ContactFormData;
use App\Service\HomepageProvider;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse; // ⬅️ add this
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

final class ContactController extends AbstractController
{
    #[Route('/contact/submit', name: 'contact_submit', methods: ['POST'])]
    public function submit(
        Request            $request,
        ValidatorInterface $validator,
        MailerInterface    $mailer,
        HomepageProvider   $homepage,
    ): Response
    {
        // 1) CSRF
        $token = (string)$request->request->get('_token', '');
        if (!$this->isCsrfTokenValid('contact_form', $token)) {
            $this->storeBack($request, [], ['form' => 'Jeton CSRF invalide.']);
            return $this->redirectToContact(); // ⬅️
        }

        // 2) Honeypot
        if (\is_string($request->request->get('website')) && $request->request->get('website') !== '') {
            // On fait comme si tout allait bien, mais on ne fait rien
            $this->markSuccess($request);
            return $this->redirectToContact(); // ⬅️
        }

        // 3) Récup données POST (trim)
        $v = static fn($k) => trim((string)$request->request->get($k, ''));
        $values = [
            'first_name' => $v('first_name'),
            'last_name'  => $v('last_name'),
            'email'      => $v('email'),
            'company'    => $v('company'),
            'phone'      => $v('phone'),
            'message'    => $v('message'),
            'budget'     => $v('budget'),
        ];

        // 4) Validation DTO
        $dto = new ContactFormData(
            $values['first_name'],
            $values['last_name'],
            $values['email'],
            $values['company'] ?: null,
            $values['phone'] ?: null,
            $values['message'],
            $values['budget'],
        );

        $violations = $validator->validate($dto);

        // 5) Validation "budget" contre les options YAML
        $contact = $homepage->getViewModel('contact'); // lit content/contact.yaml (ok si ton provider l’expose ainsi)
        $allowed = array_values(array_filter(array_map(
            fn($o) => is_array($o) ? ($o['value'] ?? null) : null,
            (array)($contact['form']['budget_options'] ?? [])
        )));
        $errors = [];
        if ($allowed && !in_array($dto->budget, $allowed, true)) {
            $errors['budget'] = 'Budget invalide.';
        }

        // 6) Mapping des violations -> erreurs par champ
        foreach ($violations as $violation) {
            $prop = $violation->getPropertyPath(); // e.g. "first_name"
            $errors[$prop] ??= $violation->getMessage();
        }

        if ($errors) {
            $this->storeBack($request, $values, $errors);
            return $this->redirectToContact(); // ⬅️
        }

        // 7) Envoi de l'email
        $email = (new TemplatedEmail())
            ->from(new Address('no-reply@votredomaine.tld', 'Côte de Lumière Technologies'))
            ->to(new Address('vous@votredomaine.tld', 'Contact site'))
            ->replyTo(new Address($dto->email, $dto->first_name . ' ' . $dto->last_name))
            ->subject('Nouveau message de contact')
            ->htmlTemplate('emails/contact.html.twig')
            ->context(['data' => $dto]);

        $mailer->send($email);

        // 8) Succès + redirection ancrée sur #contact
        $this->markSuccess($request);
        return $this->redirectToContact(); // ⬅️
    }

    // === Helpers privés ===

    /**
     * Redirige proprement vers la home ancrée sur #contact (PRG 303 + Turbo-Location).
     */
    private function redirectToContact(): RedirectResponse
    {
        $url = $this->generateUrl('app_home', ['_fragment' => 'contact']);
        $response = new RedirectResponse($url, 303);        // 303 See Other
        $response->headers->set('Turbo-Location', $url);    // pour Turbo Drive si présent
        return $response;
    }

    /**
     * Stocke valeurs et erreurs en flash pour le prochain GET (après redirect).
     * Le composant les lira via :values="contactValues" et :errors="contactErrors".
     */
    private function storeBack(Request $request, array $values, array $errors): void
    {
        $flash = $request->getSession()->getFlashBag();
        $flash->add('contact.values', $values);
        $flash->add('contact.errors', $errors);
    }

    /**
     * Marque le succès (message vert dans le composant).
     */
    private function markSuccess(Request $request): void
    {
        $flash = $request->getSession()->getFlashBag();
        $flash->add('contact.success', true);
    }
}
