<?php

namespace App\Model;

use AutoMapper\Attribute\Mapper;
use AutoMapper\Attribute\MapTo;

#[Mapper(source: 'array', target: 'array')]
class Bookmark
{
    public string $id;
    #[MapTo(dateTimeFormat: \DateTimeInterface::ATOM)]
    public \DateTimeImmutable $createdAt;
    public string $title;
    public string $url;
    /** @var array<Tag> */
    public array $tags;
    public User $owner;
    public ?FileObject $mainImage = null;
    public ?FileObject $pdf = null;
    public ?FileObject $archive = null;
    public bool $isPublic = false;
}
