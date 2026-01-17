<?php

namespace App\Response;

use App\ActivityPub\Dto\PersonActor;
use App\ActivityPub\Dto\PersonActorEndpoints;
use App\ActivityPub\Dto\PersonActorPublicKey;
use App\ActivityPub\Dto\WebFinger;
use App\ActivityPub\Dto\WebFingerLink;
use App\Config\RouteAction;
use App\Config\RouteType;
use App\Entity\Account;
use App\Service\UrlGenerator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class ActivityPubResponseBuilder
{
    public function __construct(
        private UrlGenerator $urlGenerator,
        #[Autowire('%instanceHost%')]
        private string $instanceHost,
        private SerializerInterface $serializer,
    ) {
    }

    // TODO
    public function todo(): JsonResponse
    {
        return $this->jsonActivity();
    }

    public function ok(): JsonResponse
    {
        return $this->jsonActivity();
    }

    public function profile(Account $account): JsonResponse
    {
        $person = new PersonActor();

        $person->id = $account->uri;
        $person->name = $account->username;
        $person->preferredUsername = $account->username;
        $person->inbox = $this->urlGenerator->generate(
            RouteType::ActivityPub,
            RouteAction::Inbox,
            ['username' => $account->username]
        );
        $person->outbox = $this->urlGenerator->generate(
            RouteType::ActivityPub,
            RouteAction::Outbox,
            ['username' => $account->username]
        );
        $person->url = $account->uri;
        $person->published = $account->createdAt->format(\DATE_ATOM);
        $person->following = $this->urlGenerator->generate(
            RouteType::ActivityPub,
            RouteAction::Following,
            ['username' => $account->username]
        );
        $person->followers = $this->urlGenerator->generate(
            RouteType::ActivityPub,
            RouteAction::Follower,
            ['username' => $account->username]
        );
        $person->publicKey = new PersonActorPublicKey();
        $person->publicKey->owner = $account->uri;
        $person->publicKey->id = $account->uri . '#main-key';
        $person->publicKey->publicKeyPem = $account->publicKey
            ?? throw new \RuntimeException('Missing publicKey for account.');
        $person->endpoints = new PersonActorEndpoints();
        $person->endpoints->sharedInbox = $this->urlGenerator->generate(
            RouteType::ActivityPub,
            RouteAction::SharedInbox
        );

        return $this->jsonActivity($this->serializer->serialize($person, 'json'));
    }

    public function webfinger(string $username): JsonResponse
    {
        $profileUrl = $this->urlGenerator->generate(
            RouteType::Profile, RouteAction::Get,
            ['username' => $username]
        );

        $webfinger = new WebFinger();
        $webfinger->subject = "acct:{$username}@{$this->instanceHost}";
        $webfinger->aliases = [$profileUrl];
        $webfingerLink = new WebFingerLink();
        /* @noinspection HttpUrlsUsage */
        $webfingerLink->rel = 'http://webfinger.net/rel/profile-page';
        $webfingerLink->type = 'text/html';
        $webfingerLink->href = $profileUrl;
        $webfinger->links[] = $webfingerLink;
        $webfingerLink = new WebFingerLink();
        $webfingerLink->rel = 'self';
        $webfingerLink->type = 'application/activity+json';
        $webfingerLink->href = $profileUrl;
        $webfinger->links[] = $webfingerLink;

        return $this->jsonJrd($this->serializer->serialize($webfinger, 'json'));
    }

    private function jsonActivity(mixed $data = null): JsonResponse
    {
        $response = new JsonResponse($data, json: null !== $data)->setEncodingOptions(\JSON_UNESCAPED_SLASHES);
        $response->headers->set('Content-Type', 'application/activity+json');

        return $response;
    }

    private function jsonJrd(mixed $data = null): JsonResponse
    {
        $response = new JsonResponse($data, json: null !== $data)->setEncodingOptions(\JSON_UNESCAPED_SLASHES);
        $response->headers->set('Content-Type', 'application/jrd+json; charset=utf-8');

        return $response;
    }
}
