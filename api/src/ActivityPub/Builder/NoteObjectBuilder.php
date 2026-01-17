<?php

namespace App\ActivityPub\Builder;

use App\ActivityPub\Dto\DocumentObject;
use App\ActivityPub\Dto\HashtagObject;
use App\ActivityPub\Dto\NoteObject;
use App\Config\RouteAction;
use App\Config\RouteType;
use App\Entity\Bookmark;
use App\Entity\Follower;
use App\Service\UrlGenerator;

final readonly class NoteObjectBuilder
{
    public function __construct(
        private UrlGenerator $urlGenerator,
        private string $storageDefaultPublicPath,
        private string $baseUri,
    ) {
    }

    /**
     * @param array<int, Follower> $followers
     */
    public function buildFromBookmark(Bookmark $bookmark, array $followers): NoteObject
    {
        $actorAccount = $bookmark->account;
        $url = $bookmark->url;

        $scheme = parse_url($url, \PHP_URL_SCHEME) . '://';
        $urlVisible = preg_replace("`^{$scheme}`", '', $url);
        $id = $this->urlGenerator->generate(
            RouteType::Profile,
            RouteAction::Get,
            ['username' => $actorAccount->username, 'id' => $bookmark->id],
        );
        $content = \sprintf('<p>%s <a href="%s" target="_blank" rel="nofollow noopener noreferrer"><span class="invisible">%s</span><span class=">%s</span></a>', $bookmark->title, $url, $scheme, $urlVisible);

        $noteObject = new NoteObject();
        $noteObject->id = $id;
        $noteObject->url = $id;
        $noteObject->published = $bookmark->createdAt->format(\DATE_ATOM);
        $noteObject->attributedTo = $actorAccount->uri;

        $cc = [];
        foreach ($followers as $follower) {
            $cc[] = $follower->account->uri;
        }
        $noteObject->cc = $cc;

        $attachments = [];
        if ($mainImage = $bookmark->mainImage) {
            $documentObject = new DocumentObject();
            $documentObject->url =
                // TODO use flysystem instead
                $this->baseUri . $this->storageDefaultPublicPath . '/' . $mainImage->filePath;
            $documentObject->mediaType = $mainImage->mime;
            $documentObject->name = 'Bookmark Cover';
            $attachments[] = $documentObject;
        }

        if ($archive = $bookmark->archive) {
            $documentObject = new DocumentObject();
            $documentObject->url =
                // TODO use flysystem instead
                $this->baseUri . $this->storageDefaultPublicPath . '/' . $archive->filePath;
            $documentObject->mediaType = $archive->mime;
            $documentObject->name = 'Bookmark Archive';
            $attachments[] = $documentObject;
        }
        $noteObject->attachment = $attachments;

        $hashtags = [];
        foreach ($bookmark->userTags as $tag) {
            if (!$tag->isPublic) {
                continue;
            }
            $tagUrl = $this->urlGenerator->generate(
                RouteType::ProfileBookmarks,
                RouteAction::Collection,
                ['username' => $bookmark->account->username, 'tags' => $tag->slug],
            );
            $hashtag = new HashtagObject();
            $hashtag->href = $tagUrl;
            $hashtag->name = '#' . $tag->name;
            $hashtags[] = $hashtag;
            $content .= \sprintf('<a href="%s" target="_blank" rel="nofollow noopener noreferrer tag" class="mention hashtag">#<span>%s</span></a>', $tagUrl, $tag->name);
        }
        $noteObject->tags = $hashtags;

        $noteObject->content = "{$content}</p>";

        return $noteObject;
    }

    /* {
        "type": "Note",
        "id": "https:\/\/api2.bookmarkhive.test\/profile\/bob?id=019bc66e-bb97-799a-a227-16ad7eb33bd8",
        "published": "2026-01-01T00:00:00+00:00",
        "url": "https:\/\/api2.bookmarkhive.test\/profile\/bob?id=019bc66e-bb97-799a-a227-16ad7eb33bd8",
        "attributedTo": "https:\/\/api2.bookmarkhive.test\/profile\/bob",
        "atomUri": "https:\/\/api2.bookmarkhive.test\/profile\/bob?id=019bc66e-bb97-799a-a227-16ad7eb33bd8",
        "conversation": null,
        "content": "<p>Cursor Blog: Scaling Agents <a href=\"https:\/\/cursor.com\/blog\/scaling-agents\" target=\"_blank\" rel=\"nofollow noopener noreferrer\"><span class=\"invisible\">https:\/\/<\/span><span class=\">cursor.com\/blog\/scaling-agents<\/span><\/a><a href=\"https:\/\/api2.bookmarkhive.test\/profile\/bob\/bookmarks?tags=writing\" target=\"_blank\" rel=\"nofollow noopener noreferrer tag\" class=\"mention hashtag\">#<span>writing<\/span><\/a><\/p>",
        "to": ["https:\/\/www.w3.org\/ns\/activitystreams#Public"],
        "cc": ["https:\/\/api1.bookmarkhive.test\/profile\/alice"],
        "contentMap": {
            "en": "<p>Cursor Blog: Scaling Agents <a href=\"https:\/\/cursor.com\/blog\/scaling-agents\" target=\"_blank\" rel=\"nofollow noopener noreferrer\"><span class=\"invisible\">https:\/\/<\/span><span class=\">cursor.com\/blog\/scaling-agents<\/span><\/a><a href=\"https:\/\/api2.bookmarkhive.test\/profile\/bob\/bookmarks?tags=writing\" target=\"_blank\" rel=\"nofollow noopener noreferrer tag\" class=\"mention hashtag\">#<span>writing<\/span><\/a><\/p>"
        },
        "attachment": [
            {
                "type": "Document",
                "url": "https:\/\/api2.bookmarkhive.test\/storage\/default\/efe\/37c\/efe37c42288907d3c2bb7a666392a34ead468bfdd725dd34ac.png",
                "mediaType": "image\/png",
                "name": "Bookmark Cover",
                "blurhash": null,
                "focalPoint": [0,0],
                "width": null,
                "height": null
            },
            {
                "type": "Document",
                "url": "https:\/\/api2.bookmarkhive.test\/storage\/default\/452\/dc2\/452dc2cb029e61e3b85b4346352d49a67203e96ddab6b68903.gz",
                "mediaType": "application\/gzip",
                "name": "Bookmark Archive",
                "blurhash": null,
                "focalPoint": [0,0],
                "width": null,
                "height": null
            }
        ],
        "tags": [
            {
                "type": "Hashtag",
                "href": "https:\/\/api2.bookmarkhive.test\/profile\/bob\/bookmarks?tags=writing",
                "name": "#writing"
            }
        ],
        "replies": null,
        "summary": null,
        "inReplyTo": null,
        "sensitive": false,
        "inReplyToAtomUri": null
    } */
    public function parseToBookmark(NoteObject $noteObject): Bookmark
    {
        $bookmark = new Bookmark();

        // TODO add tests
        // https://regex101.com/r/7xUY7D/3 (1,2, ...)
        $hrefRegex = '`href="([^"]+)"`miu';
        $titleRegex = '`(.*?)<a `misu';

        $html = $noteObject->content;

        $urls = [];
        preg_match($hrefRegex, $html, $urls);
        $bookmark->url = $urls[1]
            ?? throw new \LogicException('Did not find an url in Note Object.');

        $titles = [];
        preg_match($titleRegex, $html, $titles);
        $title = $titles[1]
            ?? throw new \LogicException('Did not find a title in Note Object.');
        $title = mb_trim(strip_tags($title));
        if (0 === mb_strlen($title)) {
            throw new \LogicException('Did not find a title in Note Object.');
        }
        $bookmark->title = $title;
        $bookmark->instance = '';

        foreach ($noteObject->tags as $tag) {
            // Create tag??
            // TODO shadow tags?
        }

        foreach ($noteObject->attachment as $object) {
            // Create fileObject??
            // TODO shadow fileObjects?
        }

        return $bookmark;
    }
}
