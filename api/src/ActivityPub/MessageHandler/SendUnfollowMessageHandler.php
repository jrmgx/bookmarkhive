<?php

namespace App\ActivityPub\MessageHandler;

use App\ActivityPub\Message\SendUnfollowMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class SendUnfollowMessageHandler
{
    public function __invoke(SendUnfollowMessage $message): void
    {
        // TODO
    }
}
