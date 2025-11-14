<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\PageSection;
use App\Repository\PageSectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class PageSectionOrderController extends AbstractController
{
    #[Route(
        path: '/admin/page-sections/reorder',
        name: 'admin_page_section_reorder',
        methods: ['POST']
    )]
    public function reorder(
        Request $request,
        PageSectionRepository $pageSectionRepository,
        EntityManagerInterface $em
    ): JsonResponse {
        $data = \json_decode((string) $request->getContent(), true);

        if (!\is_array($data) || !isset($data['ids']) || !\is_array($data['ids'])) {
            return new JsonResponse(['error' => 'Invalid payload'], JsonResponse::HTTP_BAD_REQUEST);
        }

        /**
         * On va regrouper les sections par page, pour que chaque page ait sa
         * numérotation propre : 1, 2, 3... (et pas un compteur global).
         *
         * $groups[pageId] = [PageSection, PageSection, ...] dans le nouvel ordre.
         */
        $groups = [];

        foreach ($data['ids'] as $id) {
            $section = $pageSectionRepository->find($id);

            if (!$section instanceof PageSection) {
                continue;
            }

            $page   = $section->getPage();
            $pageId = $page?->getId();

            if ($pageId === null) {
                continue;
            }

            $groups[$pageId][] = $section;
        }

        // Maintenant, on renumérote par page
        foreach ($groups as $pageId => $sections) {
            $position = 1;

            foreach ($sections as $section) {
                $section->setPosition($position++);
            }
        }

        $em->flush();

        return new JsonResponse(['status' => 'ok']);
    }
}
