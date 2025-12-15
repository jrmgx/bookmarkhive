<?php

namespace App\Model;

use AutoMapper\Attribute\Mapper;

#[Mapper(source: 'array', target: 'array')]
class FileObject
{
    public string $contentUrl;
    public int $size;
    public string $mime;
}
