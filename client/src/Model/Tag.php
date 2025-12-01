<?php

namespace App\Model;

use App\Service\ApiService;

class Tag
{
    public const string LAYOUT_DEFAULT = 'default';
    public const string LAYOUT_EMBEDDED = 'embedded';
    public const string LAYOUT_IMAGE = 'image';
    // public const string LAYOUT_POST = 'post';

    public string $name;
    public bool $isPublic = false;
    // Will be saved in meta
    public bool $pinned = false;
    public string $layout = self::LAYOUT_DEFAULT;

    public function __construct(
    ) {
    }

    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'isPublic' => $this->isPublic,
            'meta' => [
                ApiService::META_PREFIX . 'pinned' => $this->pinned,
                ApiService::META_PREFIX . 'layout' => $this->layout,
            ],
        ];
    }
}
