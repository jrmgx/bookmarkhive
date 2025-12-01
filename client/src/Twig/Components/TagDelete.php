<?php

namespace App\Twig\Components;

use App\Service\ApiService;
use Pentiminax\UX\SweetAlert\Twig\Components\ConfirmButton;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;

#[AsLiveComponent(template: '@SweetAlert/components/ConfirmButton.html.twig')]
class TagDelete extends ConfirmButton
{
    public function __construct(
        private readonly ApiService $apiService,
        private readonly UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @param array<mixed> $result
     * @param array<mixed> $args
     */
    #[LiveAction]
    public function callbackAction(#[LiveArg] array $result, #[LiveArg] array $args = []): mixed
    {
        parent::callbackAction($result, $args);

        if ($this->result->isConfirmed) {
            $this->apiService->deleteTag($args['slug']);

            // TODO same as TagEdit
            return new RedirectResponse($this->urlGenerator->generate('index'));
            // $this->emit('editTagFinish');
        }

        return null;
    }
}
