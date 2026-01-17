<?php

namespace App\ActivityPub\Dto;

final class CollectionPage
{
    /* {
        "type":"CollectionPage",
        "next":"https://activitypub.academy/users/braulus_aelamun/statuses/115903743533604527/replies?only_other_accounts=true&page=true",
        "partOf":"https://activitypub.academy/users/braulus_aelamun/statuses/115903743533604527/replies",
        "items":[]
    } */
    public string $type = 'CollectionPage';
    public string $next;
    public string $partOf;
    /** @var array<mixed> */
    public array $items = [];
}
