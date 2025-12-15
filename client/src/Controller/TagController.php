<?php

namespace App\Controller;

use App\Form\TagEditType;
use App\Service\ApiService;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tag', name: 'tag_')]
final class TagController extends AbstractController
{
    public function __construct(
        private readonly ApiService $apiService,
    ) {
    }

    /** @return array<mixed> */
    #[Route('/list', name: 'list')]
    #[Template('controllers/tag/list.html.twig')]
    public function list(
        #[MapQueryParameter(name: 'tags')] string $tagQueryString = '',
    ): array {
        $selectedTagSlugs = explode(',', $tagQueryString);
        $tags = $this->apiService->getTags();

        return compact('tags', 'selectedTagSlugs');
    }

    /** @return array<mixed> */
    #[Route('/{slug}/edit', name: 'edit')]
    #[Template('controllers/tag/edit.html.twig')]
    public function edit(Request $request, string $slug): array
    {
        // TODO make a param converter
        $tag = $this->apiService->getTag($slug)
            ?? throw new \RuntimeException('This tag does not exist.');

        $form = $this->createForm(TagEditType::class, $tag, [
            'action' => $this->generateUrl('tag_edit', ['slug' => $slug]),
        ]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->apiService->updateTag($slug, $tag);
            $turboFullRefresh = $this->generateUrl('index');

            return compact('tag', 'form', 'turboFullRefresh');
        }

        return compact('tag', 'form');
    }
}
