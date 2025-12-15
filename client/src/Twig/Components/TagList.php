<?php

namespace App\Twig\Components;

use App\Model\Tag as TagModel;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
final class TagList
{
    /** @var array<TagModel> */
    public array $tags;
    /** @var array<string> */
    public array $selectedTagSlugs = [];
    /** @var array<TagModel> calculated value */
    public array $pinnedTags = [];

    /**
     * @param array<TagModel> $tags
     */
    public function mount(array $tags): void
    {
        $this->pinnedTags = array_filter($tags, fn (TagModel $tag) => $tag->pinned);
    }
}
