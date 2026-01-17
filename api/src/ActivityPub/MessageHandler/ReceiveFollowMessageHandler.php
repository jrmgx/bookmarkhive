<?php

namespace App\ActivityPub\MessageHandler;

use App\ActivityPub\AccountFetch;
use App\ActivityPub\Dto\AcceptFollowActivity;
use App\ActivityPub\Dto\FollowActivity;
use App\ActivityPub\Message\ReceiveFollowMessage;
use App\ActivityPub\SignatureHelper;
use App\Entity\Follower;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsMessageHandler]
final readonly class ReceiveFollowMessageHandler
{
    public function __construct(
        private SerializerInterface $serializer,
        private EntityManagerInterface $entityManager,
        private AccountFetch $accountFetch,
        private HttpClientInterface $httpClient,
    ) {
    }

    public function __invoke(ReceiveFollowMessage $message): void
    {
        // We must create a Follower entity and send an Accept activity
        $followActivity = $this->serializer->deserialize($message->payload, FollowActivity::class, 'json');

        $account = $this->accountFetch->fetchFromUri($followActivity->actor);
        $user = $this->accountFetch->fetchFromUri($followActivity->object)->owner
            ?? throw new UnrecoverableMessageHandlingException('No user matching this object.');

        $follower = new Follower();
        $follower->account = $account;
        $follower->owner = $user;

        $this->entityManager->persist($follower);
        $this->entityManager->flush();

        $actorAccount = $follower->owner->account;
        $objectAccount = $follower->account;
        $url = $objectAccount->inboxUrl
            ?? throw new UnrecoverableMessageHandlingException('No inbox url for actor.');

        $acceptActivity = new AcceptFollowActivity();
        $acceptActivity->actor = $user->account->uri;
        $acceptActivity->object = $followActivity;

        $payload = $this->serializer->serialize($acceptActivity, 'json');

        $signatureHeaders = SignatureHelper::build(
            url: $url,
            keyId: $actorAccount->keyId,
            privateKey: $actorAccount->privateKey
                     ?? throw new UnrecoverableMessageHandlingException('No private key for actor.'),
            payload: $payload,
        );

        $response = $this->httpClient->request('POST', $url, [
            'headers' => $signatureHeaders,
            'body' => $payload,
        ]);

        if ($response->getStatusCode() >= 400) {
            throw new \LogicException('Error when sending the Accept Follow Activity: ' . $response->getContent(false));
        }
    }
}
