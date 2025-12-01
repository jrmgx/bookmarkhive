<?php

namespace App\Twig\Components;

use App\Form\BookmarkTagType;
use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsLiveComponent]
class Bookmark extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    /**
     * @var array<mixed> OpenAPI representation of a Bookmark
     */
    #[LiveProp]
    public array $bookmark;

    #[LiveProp]
    public array $tags;

    public function __construct(
        private readonly ApiService $apiService,
    ) {
    }

    #[LiveAction]
    public function selectTag(#[LiveArg] string $tagName): void
    {
        $this->emit('selectTag', ['tagName' => $tagName]);
    }

    #[LiveAction]
    public function deselectTag(#[LiveArg] string $tagName): void
    {
        $this->emit('deselectTag', ['tagName' => $tagName]);
    }

    #[LiveAction]
    public function save()
    {
        dump('bookmark:save', $this->bookmark, $this->tags);
        $this->submitForm();
        $data = $this->getForm()->getData();
        dump($data);
        $this->apiService->updateBookmarkTags($this->bookmark['id'], $data['tags'], $this->tags);

        // TODO here we would prefer to re-use this but it does not work (so we reload the page)
        // the next invoke of that component does not contain the new tag (bug?)
        // $this->tag = null;
        // $this->resetForm();
        // $this->emit('editTagFinish');
        return $this->redirectToRoute('index');
    }

    protected function instantiateForm(): FormInterface
    {
        dump($this->bookmark);
        $tags = $this->bookmark['tags'] ?? [];
        $tagNames = array_map(fn (array $t) => $t['name'], $tags);
        $bookmarkModel = ['tags' => array_combine($tagNames, $tagNames)];
        dump($bookmarkModel);
        return $this->createForm(BookmarkTagType::class, $bookmarkModel, options: [
            'tagList' => $this->tags,
        ]); // $this->bookmark);
    }

    /**
     * Prevent sending an AJAX request on each input change.
     *
     * @phpstan-ignore-next-line
     */
    private function getDataModelValue(): string
    {
        return 'norender|*';
    }
}
