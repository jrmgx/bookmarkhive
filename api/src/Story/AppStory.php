<?php

namespace App\Story;

use App\Factory\AccountFactory;
use App\Factory\BookmarkFactory;
use App\Factory\InstanceTagFactory;
use App\Factory\UserFactory;
use App\Factory\UserTagFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Zenstruck\Foundry\Attribute\AsFixture;
use Zenstruck\Foundry\Story;

#[AsFixture(name: 'main')]
final class AppStory extends Story
{
    public function build(): void
    {
        $user = UserFactory::createOne(['username' => 'one']);
        $account = AccountFactory::createOne(['username' => 'one', 'owner' => $user]);
        $instanceTagPublic = InstanceTagFactory::createOne(['name' => 'Tag Public']);
        $instanceTagPrivate = InstanceTagFactory::createOne(['name' => 'Tag Private']);
        $userTagPublic = UserTagFactory::createOne([
            'name' => 'Tag Public',
            'owner' => $user,
            'isPublic' => true,
        ]);
        $userTagPrivate = UserTagFactory::createOne([
            'name' => 'Tag Private',
            'owner' => $user,
            'isPublic' => false,
        ]);
        BookmarkFactory::createMany(10, [
            'account' => $account,
            'isPublic' => true,
            'userTags' => new ArrayCollection([$userTagPublic, $userTagPrivate]),
            'instanceTags' => new ArrayCollection([$instanceTagPublic, $instanceTagPrivate]),
        ]);
        BookmarkFactory::createMany(10, [
            'account' => $account,
            'isPublic' => false,
            'userTags' => new ArrayCollection([$userTagPublic, $userTagPrivate]),
            'instanceTags' => new ArrayCollection([$instanceTagPublic, $instanceTagPrivate]),
        ]);
    }
}
