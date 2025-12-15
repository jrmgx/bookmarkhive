<?php

namespace App\Controller;

use App\Model\Tag as TagModel;
use App\Service\ApiService;
use App\Service\AuthContext;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
final class AppController extends AbstractController
{
    public function __construct(
        private readonly AuthContext $authContext,
        private readonly ApiService $apiService,
    ) {
    }

    /** @return array<mixed> */
    #[Route('', name: 'index')]
    #[Template('controllers/index.html.twig')]
    public function index(
        #[MapQueryParameter(name: 'tags')] string $tagQueryString = '',
    ): array {
        // TODO put in security
        if (!$this->authContext->isLoggedIn()) {
            // TODO
            // return $this->redirectToRoute('login');
        }

        $selectedTagSlugs = explode(',', $tagQueryString);
        $tags = $this->apiService->getTags();
        $bookmarks = $this->apiService->getBookmarks(tags: $tagQueryString);
        $layout = TagModel::LAYOUT_DEFAULT;

        return compact('tags', 'bookmarks', 'selectedTagSlugs', 'layout');
    }
}
