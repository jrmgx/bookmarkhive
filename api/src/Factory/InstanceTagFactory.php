<?php

namespace App\Factory;

use App\Entity\InstanceTag;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<InstanceTag>
 */
final class InstanceTagFactory extends PersistentObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return InstanceTag::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        /** @var string $name */
        $name = self::faker()->words(2, asText: true);

        return [
            'name' => ucfirst($name),
        ];
    }
}
