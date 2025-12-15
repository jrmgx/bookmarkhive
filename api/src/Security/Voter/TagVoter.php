<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Tag;
use App\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<string, Tag>
 */
final class TagVoter extends Voter
{
    public const string OWNER = 'TAG_OWNER';
    public const string PUBLIC = 'TAG_PUBLIC';

    protected function supports(string $attribute, mixed $subject): bool
    {
        return \in_array($attribute, [self::OWNER]) && $subject instanceof Tag; // TODO simplify
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var Tag $tag */
        $tag = $subject;

        if (self::OWNER === $attribute) {
            $user = $token->getUser();

            if (!$user instanceof User) {
                return false;
            }

            return $tag->owner === $user;
        }

        return false;
    }
}
