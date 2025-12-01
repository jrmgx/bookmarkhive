<?php

/** @noinspection PhpUnusedPrivateMethodInspection, PhpUnused */

namespace App\Twig\Components;

use App\Form\UsernamePasswordType;
use App\Model\Tag as TagModel;
use App\Model\UsernamePassword;
use App\Service\ApiService;
use App\Service\AuthContext;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveArg;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentWithFormTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class AppContext extends AbstractController
{
    use ComponentWithFormTrait;
    use DefaultActionTrait;

    #[LiveProp]
    public ?UsernamePassword $initialFormData = null;

    /** @var array<string, mixed> OpenAPI representation of a list of Tags indexed by tag.name */
    #[LiveProp]
    public array $tags = [];

    /** @var array<string, mixed> OpenAPI representation of a list of Tags indexed by tag.name */
    #[LiveProp]
    public array $selectedTags = [];

    /** @var array<string, mixed> OpenAPI representation of a list of Tags indexed by tag.name */
    #[LiveProp]
    public array $pinnedTags = [];

    /** @var ?array<mixed> OpenAPI representation of a Tag */
    #[LiveProp]
    public ?array $editingTag = null;

    /** @var array<mixed> OpenAPI representation of a list of Bookmarks */
    #[LiveProp]
    public array $bookmarks = [];

    #[LiveProp]
    public string $layout = TagModel::LAYOUT_DEFAULT;

    public function __construct(
        private readonly AuthContext $contextService,
        private readonly ApiService $apiService,
    ) {
    }

    public function mount(): void
    {
        if (!$this->isLoggedIn()) {
            return;
        }

        $this->loadTags();
        $this->loadBookmarks();
        $this->loadLayout();
    }

    #[LiveAction]
    public function login(): Response
    {
        $this->submitForm();

        /** @var UsernamePassword $data */
        $data = $this->getForm()->getData();

        $jwt = $this->apiService->login($data->email, $data->password);
        $response = $this->redirectToRoute('index');

        return $this->contextService->setLoggedIn($jwt, $response);
    }

    #[LiveAction]
    #[LiveListener('selectTag')]
    public function selectTag(#[LiveArg] string $tagName): void
    {
        if (!$this->tags[$tagName]) {
            return;
        }

        $this->selectedTags[$tagName] = $this->tags[$tagName];

        $this->loadBookmarks();
        $this->loadLayout();
    }

    #[LiveAction]
    #[LiveListener('deselectTag')]
    public function deselectTag(#[LiveArg] string $tagName): void
    {
        if (!$this->selectedTags[$tagName]) {
            return;
        }

        unset($this->selectedTags[$tagName]);

        $this->loadBookmarks();
        $this->loadLayout();
    }

    #[LiveAction]
    public function editTag(#[LiveArg] string $tagName): void
    {
        if (!$this->tags[$tagName]) {
            return;
        }

        $this->editingTag = $this->tags[$tagName];
    }

    #[LiveListener('editTagFinish')]
    public function editTagFinish(): void
    {
        $this->editingTag = null;
        $this->selectedTags = [];

        $this->loadTags();
        $this->loadBookmarks();
        $this->loadLayout();
    }

    public function isLoggedIn(): bool
    {
        return $this->contextService->isLoggedIn();
    }

    /**
     * Login form.
     *
     * @return FormInterface<UsernamePassword|null>
     */
    protected function instantiateForm(): FormInterface
    {
        return $this->createForm(UsernamePasswordType::class, $this->initialFormData);
    }

    private function loadBookmarks(): void
    {
        $tagNames = array_map(fn (array $t) => $t['name'], $this->selectedTags);

        $this->bookmarks = $this->apiService->getBookmarks(tags: implode(',', $tagNames));
    }

    private function loadLayout(): void
    {
        foreach ($this->selectedTags as $tag) {
            if (($tag['meta'][ApiService::META_PREFIX . 'layout'] ?? TagModel::LAYOUT_DEFAULT) !== TagModel::LAYOUT_DEFAULT) {
                $this->layout = $tag['meta'][ApiService::META_PREFIX . 'layout'];

                return;
            }
        }

        $this->layout = TagModel::LAYOUT_DEFAULT;
    }

    private function loadTags(): void
    {
        $getTags = $this->apiService->getTags();

        $tags = [];
        $pinnedTags = [];
        foreach ($getTags as $getTag) {
            $tags[$getTag['name']] = $getTag;
            if ($getTag['meta'][ApiService::META_PREFIX . 'pinned'] ?? false) {
                $pinnedTags[$getTag['name']] = $getTag;
            }
        }

        uksort($tags, strnatcasecmp(...));
        uksort($pinnedTags, strnatcasecmp(...));

        $this->tags = $tags;
        $this->pinnedTags = $pinnedTags;
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
