<?php

namespace App\ActivityPub\Dto;

final class Collection
{
    /* {
        "id":"https://activitypub.academy/users/braulus_aelamun/statuses/115903743533604527/replies",
        "type":"Collection",
        "first":{
            "type":"CollectionPage",
            "next":"https://activitypub.academy/users/braulus_aelamun/statuses/115903743533604527/replies?only_other_accounts=true&page=true",
            "partOf":"https://activitypub.academy/users/braulus_aelamun/statuses/115903743533604527/replies",
            "items":[]
        }
    } */
    public string $type = 'Collection';
    public string $id;
    public CollectionPage $first;
}
