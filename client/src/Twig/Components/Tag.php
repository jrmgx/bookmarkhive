<?php

namespace App\Twig\Components;

use App\Model\Tag as TagModel;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Tag extends AbstractController
{
    public TagModel $tag;
    /** @var array<string> */
    public array $selectedTagSlugs = [];
    public bool $showInline = false;
}
