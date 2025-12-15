<?php

namespace App\Model;

use App\Service\ApiService;
use AutoMapper\Attribute\MapFrom;
use AutoMapper\Attribute\Mapper;
use AutoMapper\Attribute\MapTo;

#[Mapper(source: 'array', target: 'array')]
class Tag
{
    public const string LAYOUT_DEFAULT = 'default';
    public const string LAYOUT_EMBEDDED = 'embedded';
    public const string LAYOUT_IMAGE = 'image';
    // public const string LAYOUT_POST = 'post';

    #[MapTo(target: 'array', ignore: true)]
    public string $id;
    #[MapTo(target: 'array', ignore: true)]
    public string $slug;
    public string $name;
    public bool $isPublic = false;
    #[MapTo(target: 'array', property: 'meta', transformer: [self::class, 'transformMeta'])]
    #[MapTo(target: 'array', ignore: true)]
    #[MapFrom(source: 'array', property: 'meta', transformer: [self::class, 'transformPinned'])]
    public bool $pinned = false;
    #[MapTo(target: 'array', ignore: true)]
    #[MapFrom(source: 'array', property: 'meta', transformer: [self::class, 'transformLayout'])]
    public string $layout = self::LAYOUT_DEFAULT;

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * We use one transform to fill up meta with two values, but that's fine.
     *
     * @param array<mixed> $context
     *
     * @return array<string, mixed>
     */
    public static function transformMeta(bool $property, self $source, array $context): array
    {
        return [
            ApiService::META_PREFIX . 'pinned' => $source->pinned,
            ApiService::META_PREFIX . 'layout' => $source->layout,
        ];
    }

    /**
     * @param array<mixed> $property
     * @param array<mixed> $source
     * @param array<mixed> $context
     */
    public static function transformPinned(array $property, array $source, array $context): bool
    {
        return (bool) ($property[ApiService::META_PREFIX . 'pinned'] ?? false);
    }

    /**
     * @param array<mixed> $property
     * @param array<mixed> $source
     * @param array<mixed> $context
     */
    public static function transformLayout(array $property, array $source, array $context): string
    {
        return (string) ($property[ApiService::META_PREFIX . 'layout'] ?? self::LAYOUT_DEFAULT);
    }
}
