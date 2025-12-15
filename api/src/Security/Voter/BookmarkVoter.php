<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Bookmark;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Bookmark>
 */
final class BookmarkVoter extends Voter
{
    public const string OWNER = 'BOOKMARK_OWNER';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::OWNER]) && $subject instanceof Bookmark; // TODO simplify
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var Bookmark $bookmark */
        $bookmark = $subject;

        if (self::OWNER === $attribute) {
            $user = $token->getUser();

            if (!$user instanceof User) {
                return false;
            }

            return $bookmark->owner === $user;
        }

        return false;
    }
}
