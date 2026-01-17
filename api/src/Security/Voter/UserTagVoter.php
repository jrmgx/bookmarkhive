<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\User;
use App\Entity\UserTag;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, UserTag>
 */
final class UserTagVoter extends Voter
{
    public const string OWNER = 'TAG_OWNER';
    public const string PUBLIC = 'TAG_PUBLIC';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return self::OWNER === $attribute && $subject instanceof UserTag;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var UserTag $userTag */
        $userTag = $subject;

        if (self::OWNER === $attribute) {
            $user = $token->getUser();

            if (!$user instanceof User) {
                return false;
            }

            return $userTag->owner === $user;
        }

        return false;
    }
}
