<?php

namespace App\Controller;

use App\Form\BookmarkTagType;
use App\Model\Bookmark;
use App\Service\ApiService;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bookmark', name: 'bookmark_')]
final class BookmarkController extends AbstractController
{
    public function __construct(
        private readonly ApiService $apiService,
    ) {
    }

    /** @return array<mixed>|Response */
    #[Route('/{id}/edit', name: 'edit')]
    #[Template('controllers/bookmark/edit.html.twig')]
    public function edit(Request $request, string $id): array|Response // Bookmark $bookmark): array|Response
    {
        // TODO make a param converter
        $bookmark = $this->apiService->getBookmark($id);

        $form = $this->createForm(BookmarkTagType::class, $bookmark, [
            'action' => $this->generateUrl('bookmark_edit', ['id' => $id]),
            'tagList' => $this->apiService->getTags(),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $this->apiService->

            return $this->redirectToRoute('index');
        }

        return compact('bookmark', 'form');
    }
}
