<?php

namespace App\ActivityPub\Builder;

use App\ActivityPub\Dto\CreateNoteActivity;
use App\Entity\Bookmark;
use App\Entity\Follower;

final readonly class CreateActivityBuilder
{
    public function __construct(
        private NoteObjectBuilder $noteObjectBuilder,
    ) {
    }

    /**
     * @param array<int, Follower> $followers
     */
    public function buildFromBookmark(Bookmark $bookmark, array $followers): CreateNoteActivity
    {
        $noteObject = $this->noteObjectBuilder->buildFromBookmark($bookmark, $followers);

        $createActivity = new CreateNoteActivity();
        $createActivity->id = $noteObject->id;
        $createActivity->actor = $bookmark->account->uri;
        $createActivity->published = $noteObject->published;
        $createActivity->cc = $noteObject->cc;
        $createActivity->object = $noteObject;

        return $createActivity;
    }
}
