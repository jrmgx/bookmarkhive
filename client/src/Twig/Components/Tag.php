<?php

namespace App\Twig\Components;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Tag
{
    /**
     * @var array<mixed> OpenAPI representation of a Tag
     */
    public array $tag;
}
