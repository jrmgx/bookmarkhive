<?php

/** @noinspection HttpUrlsUsage */

namespace App\ActivityPub\Dto;

use Symfony\Component\Serializer\Attribute\SerializedName;

final class CreateNoteActivity
{
    /* {
        "@context":[
            "https://www.w3.org/ns/activitystreams", {
                "ostatus":"http://ostatus.org#",
                "atomUri":"ostatus:atomUri",
                "inReplyToAtomUri":"ostatus:inReplyToAtomUri",
                "conversation":"ostatus:conversation",
                "sensitive":"as:sensitive",
                "toot":"http://joinmastodon.org/ns#",
                "votersCount":"toot:votersCount",
                "blurhash":"toot:blurhash",
                "focalPoint":{
                    "@container":"@list",
                    "@id":"toot:focalPoint"
                },
                "Hashtag":"as:Hashtag"
            }
        ],
        "id":"https://activitypub.academy/users/braulus_aelamun/statuses/115903743533604527/activity",
        "type":"Create",
        "actor":"https://activitypub.academy/users/braulus_aelamun",
        "published":"2026-01-01T00:00:00Z",
        "to":["https://www.w3.org/ns/activitystreams#Public"],
        "cc":["https://activitypub.academy/users/braulus_aelamun/followers"],
        "object":{...},
        "signature":{
            "type":"RsaSignature2017",
            "creator":"https://activitypub.academy/users/braulus_aelamun#main-key",
            "created":"2026-01-01T00:00:00Z",
            "signatureValue":"hex=="
        }
    } */
    /** @var array<mixed> */
    #[SerializedName('@context')]
    public array $context = [
        Constant::CONTEXT_URL, [
            // Fully compatible with mastodon TODO this could be simplified and stays compatible
            'ostatus' => 'http://ostatus.org#',
            'atomUri' => 'ostatus:atomUri',
            'inReplyToAtomUri' => 'ostatus:inReplyToAtomUri',
            'conversation' => 'ostatus:conversation',
            'sensitive' => 'as:sensitive',
            'toot' => 'http://joinmastodon.org/ns#',
            'votersCount' => 'toot:votersCount',
            'blurhash' => 'toot:blurhash',
            'focalPoint' => [
                '@container' => '@list',
                '@id' => 'toot:focalPoint',
            ],
            'Hashtag' => 'as:Hashtag',
        ],
    ];
    public string $type = 'Create';
    public string $id;
    public string $actor;
    public string $published;
    /** @var array<string> */
    public array $to = [Constant::PUBLIC_URL];
    /** @var array<string> */
    public array $cc;
    public NoteObject $object;
    public ?\stdClass $signature = null;
}
