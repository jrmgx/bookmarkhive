<?php

namespace App\ActivityPub\MessageHandler;

use App\ActivityPub\Builder\FollowActivityBuilder;
use App\ActivityPub\Message\SendFollowMessage;
use App\ActivityPub\Message\SendMessage;
use App\Repository\FollowingRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

#[AsMessageHandler]
final readonly class SendFollowMessageHandler
{
    public function __construct(
        private MessageBusInterface $messageBus,
        private SerializerInterface $serializer,
        private FollowingRepository $followingRepository,
        private FollowActivityBuilder $followActivityBuilder,
    ) {
    }

    public function __invoke(SendFollowMessage $message): void
    {
        $following = $this->followingRepository->find($message->followingId)
            ?? throw new UnrecoverableMessageHandlingException('No following entity matching.');

        $actorAccount = $following->owner->account;
        $objectAccount = $following->account;

        $url = $objectAccount->inboxUrl
            ?? throw new UnrecoverableMessageHandlingException('No inbox url for actor.');

        $follow = $this->followActivityBuilder->buildFromFollowing($following);

        $this->messageBus->dispatch(new SendMessage(
            payload: $this->serializer->serialize($follow, 'json'),
            url: $url,
            accountUri: $actorAccount->uri
        ));
    }
}
