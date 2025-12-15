<?php

namespace App\Controller;

use App\Model\Bookmark;
use App\Model\Tag;
use AutoMapper\AutoMapperInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Routing\Attribute\Route;

#[When('dev')]
#[Route('/debug')]
final class DebugController extends AbstractController
{
    public function __construct(
        private readonly AutoMapperInterface $autoMapper,
    ) {
    }

    /** @return array<mixed> */
    #[Route('/debug', name: 'debug')]
    #[Template('base.html.twig')]
    public function debug(): array
    {
        return [];
    }

    /** @return array<mixed> */
    #[Route('/components', name: 'components')]
    #[Template('controllers/components.html.twig')]
    public function components(): array
    {
        $tagOne = $this->buildTag();
        $tagOne->id = '019b12d4-9ea6-7dbb-9bee-c1eb3103c4ca';

        $tagSelected = $this->buildTag();
        $tagSelected->id = '019b12d4-9ea6-7dbb-9bee-c1eb3103c4cb';
        $tagSelected->name = 'Tag Selected';
        $tagSelected->slug = 'tag-selected';

        $tagFavorite = $this->buildTag();
        $tagFavorite->id = '019b12d4-9ea6-7dbb-9bee-c1eb3103c4cc';
        $tagFavorite->name = '🙂 Favorite';
        $tagFavorite->slug = 'smile-face-favorite';
        $tagFavorite->pinned = true;

        $tags = [$tagOne, $tagSelected, $tagFavorite];
        $selectedTagSlugs = [$tagSelected->slug];

        $bookmark = $this->buildBookmark();
        $bookmark->tags = [$tagOne, $tagSelected, $tagFavorite];

        $bookmarks = [
            $this->buildBookmark('Bookmark One', [$tagOne, $tagFavorite]),
            $this->buildBookmark('Bookmark Two', [$tagFavorite, $tagOne]),
            $this->buildBookmark('Bookmark Three', [$tagFavorite]),
            $this->buildBookmark('Bookmark Four', [$tagOne, $tagSelected]),
            $this->buildBookmark('Bookmark Five', [$tagFavorite]),
            $this->buildBookmark('Bookmark Six', [$tagSelected, $tagFavorite]),
            $this->buildBookmark('Bookmark Seven', [$tagFavorite]),
            $this->buildBookmark('Bookmark Eight', [$tagSelected, $tagOne, $tagFavorite]),
            $this->buildBookmark('Bookmark Nine', [$tagSelected]),
            $this->buildBookmark('Bookmark Ten', [$tagOne, $tagFavorite]),
            $this->buildBookmark('Bookmark Eleven'),
            $this->buildBookmark('Bookmark Twelve', [$tagFavorite]),
            $this->buildBookmark('Bookmark Thirteen', [$tagFavorite, $tagOne]),
            $this->buildBookmark('Bookmark Fourteen'),
        ];

        return compact('tagOne', 'tagSelected', 'tagFavorite', 'tags', 'selectedTagSlugs', 'bookmark', 'bookmarks');
    }

    private function buildTag(): Tag
    {
        $tagJson = json_decode(<<<'JSON'
            {
                "@context": "string",
                "@id": "string",
                "@type": "string",
                "id": "string",
                "name": "Tag One",
                "slug": "tag-one"
            }
            JSON, true);

        return $this->autoMapper->map($tagJson, Tag::class)
            ?? throw new \LogicException();
    }

    /**
     * @param array<Tag> $tags
     */
    private function buildBookmark(?string $title = null, array $tags = []): Bookmark
    {
        $bookmarkJson = json_decode(<<<'JSON'
            {
              "@context": "string",
              "@id": "string",
              "@type": "string",
              "id": "string",
              "createdAt": "2025-12-03T16:19:31+00:00",
              "title": "Bookmark Title",
              "url": "https://www.youtube.com/watch?v=R95ILhksGt8",
              "mainImage": {
                "@context": "string",
                "@id": "string",
                "@type": "string",
                "contentUrl": "https://picsum.photos/id/20/1200/800",
                "size": 10000,
                "mime": "image/png"
              },
              "owner": {
                "@context": "string",
                "@id": "string",
                "@type": "string",
                "username": "jerome"
              },
              "tags": [],
              "archive": {
                "@context": "string",
                "@id": "string",
                "@type": "string",
                "contentUrl": "https://picsum.photos/id/20/300/300",
                "size": 100,
                "mime": "application/octet-stream"
              },
              "pdf": {
                "@context": "string",
                "@id": "string",
                "@type": "string",
                "contentUrl": "https://picsum.photos/id/20/300/300",
                "size": 500,
                "mime": "application/pdf"
              }
            }
            JSON, true);

        $bookmark = $this->autoMapper->map($bookmarkJson, Bookmark::class)
            ?? throw new \LogicException();
        $rand = random_int(0, 200);
        if (!$bookmark->mainImage) {
            throw new \LogicException();
        }
        $bookmark->mainImage->contentUrl = "https://picsum.photos/id/{$rand}/1200/800";
        if ($title) {
            $bookmark->title = $title;
        }
        if ($tags) {
            $bookmark->tags = $tags;
        }

        return $bookmark;
    }
}
