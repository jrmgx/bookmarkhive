<?php

/** @noinspection PhpUnusedPrivateMethodInspection */

namespace App\Twig\Components;

use App\Form\TagEditType;
use App\Model\Tag as TagModel;
use App\Model\UsernamePassword;
use App\Service\ApiService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class TagEdit extends AbstractController
{
    use ComponentToolsTrait;
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    /**
     * @var array<mixed> OpenAPI representation of a Tag
     */
    #[LiveProp]
    public array $tag;

    #[LiveProp]
    public TagModel $tagModel;

    public function __construct(
        private readonly ApiService $apiService,
    ) {
    }

    /**
     * @param array<mixed> $tag OpenAPI representation of a Tag
     */
    public function mount(array $tag): void
    {
        $this->tag = $tag;
        $this->tagModel = new TagModel();
        $this->tagModel->name = (string) $tag['name'];
        $this->tagModel->isPublic = (bool) $tag['isPublic'];
        $this->tagModel->pinned = (bool) ($tag['meta'][ApiService::META_PREFIX . 'pinned'] ?? false);
        $this->tagModel->layout = (string) ($tag['meta'][ApiService::META_PREFIX . 'layout'] ?? TagModel::LAYOUT_DEFAULT);
    }

    #[LiveAction]
    public function save(): Response
    {
        $this->submitForm();
        $data = $this->getForm()->getData();
        $this->apiService->updateTag($this->tag['slug'], $data);

        // TODO here we would prefer to re-use this but it does not work (so we reload the page)
        // the next invoke of that component does not contain the new tag (bug?)
        // $this->tag = null;
        // $this->resetForm();
        // $this->emit('editTagFinish');
        return $this->redirectToRoute('index');
    }

    #[LiveAction]
    public function cancel(): Response
    {
        // TODO same as above
        return $this->redirectToRoute('index');
        // $this->emit('editTagFinish');
    }

    /**
     * Login form.
     *
     * @return FormInterface<UsernamePassword|null>
     */
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(TagEditType::class, $this->tagModel);
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
