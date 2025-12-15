<?php

namespace App\Twig\Components;

use App\Model\Bookmark as BookmarkModel;
use App\Model\Tag as TagModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Bookmark extends AbstractController
{
    public BookmarkModel $bookmark;
    public string $layout = TagModel::LAYOUT_DEFAULT;
    /** @var array<string> */
    public array $selectedTagSlugs = [];
}
