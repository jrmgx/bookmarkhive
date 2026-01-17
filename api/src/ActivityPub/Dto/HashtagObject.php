<?php

namespace App\ActivityPub\Dto;

final class HashtagObject
{
    /* {
        "type":"Hashtag",
        "href":"https://activitypub.academy/tags/start",
        "name":"#start"
    } */
    public string $type = 'Hashtag';
    public string $href;
    public string $name;
}
