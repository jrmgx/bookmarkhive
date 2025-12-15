<?php

namespace App\Service;

use App\Exception\ApiException;
use App\Model\Bookmark;
use App\Model\Tag;
use AutoMapper\AutoMapperInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class ApiService
{
    public const string META_PREFIX = 'client-o-';

    private const string BASE_URL = 'http://api'; // TODO make env

    public function __construct(
        private HttpClientInterface $httpClient,
        private AuthContext $authContext,
        private AutoMapperInterface $autoMapper,
    ) {
    }

    public function login(string $email, string $password): string
    {
        $url = self::BASE_URL . '/api/auth';

        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'email' => $email,
                'password' => $password,
            ],
        ]);

        $data = $response->toArray();

        return $data['token'] ??
            throw new ApiException('Token not found in authentication response.');
    }

    /**
     * @return array<Bookmark>
     */
    public function getBookmarks(string $tags, int $page = 1): array
    {
        if (\strlen($tags)) {
            $tags = '&tags=' . $tags;
        }
        $jwt = $this->authContext->getJwt()
            ?? throw new \RuntimeException('JWT token not found');

        $url = self::BASE_URL . '/api/users/me/bookmarks?page=' . $page . $tags;

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/json',
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new ApiException('Error when getting Bookmarks.');
        }

        $array = $response->toArray();
        if (!isset($array['collection'])) {
            throw new ApiException('Error when get Bookmarks.');
        }

        return $this->autoMapper->mapCollection($array['collection'], Bookmark::class);
    }

    public function getBookmark(string $id): ?Bookmark
    {
        $jwt = $this->authContext->getJwt()
            ?? throw new \RuntimeException('JWT token not found');

        $url = self::BASE_URL . '/api/users/me/bookmarks/' . $id;

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/json',
            ],
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new ApiException('Error when getting Bookmark.');
        }

        return $this->autoMapper->map($response->toArray(), Bookmark::class);
    }

    //    public function updateBookmarkTags(string $id, $tags, array $tagList): Bookmark
    //    {
    //        $jwt = $this->authContext->getJwt()
    //            ?? throw new \RuntimeException('JWT token not found');
    //
    //        $url = self::BASE_URL . '/api/users/me/bookmarks/' . $id;
    //        $tagIri = '/api/users/me/tags/';
    //
    //        $tagIds = [];
    //        foreach ($tags as $tag) {
    //            if ($tagList[$tag]['@id'] ?? false) {
    //                $tagData = $tagList[$tag];
    //                $tagIds[] = $tagIri . $tagData['slug']; // $tagList[$tag]['@id'];
    //            } else {
    //                // Create tag
    //                $data = $this->createTag($tag);
    //                // $tagIds[] = $data['@id']; // TODO the api does not return the right iri
    //                $tagIds[] = $tagIri . $data['slug'];
    //            }
    //        }
    //
    //        $response = $this->httpClient->request('PATCH', $url, [
    //            'headers' => [
    //                'Authorization' => 'Bearer ' . $jwt,
    //                'Content-Type' => 'application/json',
    //            ],
    //            'json' => ['tags' => $tagIds],
    //        ]);
    //
    //        return $this->autoMapper->map($response->toArray(), Bookmark::class);
    //    }

    /**
     * @return array<Tag>
     */
    public function getTags(): array
    {
        $jwt = $this->authContext->getJwt()
            ?? throw new \RuntimeException('JWT token not found');

        $url = self::BASE_URL . '/api/users/me/tags';

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/json',
            ],
        ]);

        return $this->autoMapper->mapCollection($response->toArray()['collection'], Tag::class);
    }

    public function getTag(string $slug): ?Tag
    {
        $jwt = $this->authContext->getJwt()
            ?? throw new \RuntimeException('JWT token not found');

        $url = self::BASE_URL . '/api/users/me/tags/' . $slug;

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/json',
            ],
        ]);

        return $this->autoMapper->map($response->toArray(), Tag::class);
    }

    public function createTag(string $name): Tag
    {
        $jwt = $this->authContext->getJwt()
            ?? throw new \RuntimeException('JWT token not found');

        $url = self::BASE_URL . '/api/users/me/tags';

        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/json',
            ],
            'json' => ['name' => $name],
        ]);

        return $this->autoMapper->map($response->toArray(), Tag::class)
            ?? throw new ApiException('Error when creating a new Tag.');
    }

    public function updateTag(string $slug, Tag $tag): Tag
    {
        $jwt = $this->authContext->getJwt()
            ?? throw new \RuntimeException('JWT token not found');

        $url = self::BASE_URL . '/api/users/me/tags/' . $slug;

        $response = $this->httpClient->request('PATCH', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/json',
            ],
            'json' => $this->autoMapper->map($tag, 'array'),
        ]);

        return $this->autoMapper->map($response->toArray(), Tag::class)
            ?? throw new ApiException('Error when updating this Tag.');
    }

    public function deleteTag(string $slug): void
    {
        $jwt = $this->authContext->getJwt()
            ?? throw new \RuntimeException('JWT token not found');

        $url = self::BASE_URL . '/api/users/me/tags/' . $slug;

        $this->httpClient->request('DELETE', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/json',
            ],
        ]);
    }
}
