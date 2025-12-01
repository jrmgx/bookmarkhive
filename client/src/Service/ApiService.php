<?php

namespace App\Service;

use App\Model\Tag;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiService
{
    public const string META_PREFIX = 'client-o-';

    private const string BASE_URL = 'http://api'; // TODO make env

    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly AuthContext $authContext,
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
            throw new \RuntimeException('Token not found in authentication response');
    }

    /**
     * @return array<mixed> OpenAPI representation of a list of Bookmarks
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
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        return $response->toArray()['member'];
    }

    /**
     * @return array<mixed> OpenAPI representation of a Bookmark
     */
    public function updateBookmarkTags(string $id, $tags, array $tagList): array
    {
        $jwt = $this->authContext->getJwt()
            ?? throw new \RuntimeException('JWT token not found');

        $url = self::BASE_URL . '/api/users/me/bookmarks/' . $id;
        $tagIri = '/api/users/me/tags/';

        $tagIds = [];
        foreach ($tags as $tag) {
            if ($tagList[$tag]['@id'] ?? false) {
                $tagData = $tagList[$tag];
                $tagIds[] = $tagIri . $tagData['slug']; // $tagList[$tag]['@id'];
            } else {
                // Create tag
                $data = $this->createTag($tag);
                // $tagIds[] = $data['@id']; // TODO the api does not return the right iri
                $tagIds[] = $tagIri . $data['slug'];
            }
        }

        dump($tagIds);

        $response = $this->httpClient->request('PATCH', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => ['tags' => $tagIds]
        ]);

        dump($response);
        return $response->toArray();
    }

    /**
     * @return array<mixed> OpenAPI representation of a list of Tags
     */
    public function getTags(): array
    {
        $jwt = $this->authContext->getJwt()
            ?? throw new \RuntimeException('JWT token not found');

        $url = self::BASE_URL . '/api/users/me/tags';

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/ld+json',
            ],
        ]);

        return $response->toArray()['member'];
    }

    /**
     * @return array<mixed> OpenAPI representation of a Tag
     */
    public function createTag(string $name): array
    {
        $jwt = $this->authContext->getJwt()
            ?? throw new \RuntimeException('JWT token not found');

        $url = self::BASE_URL . '/api/users/me/tags';

        $response = $this->httpClient->request('POST', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/ld+json',
            ],
            'json' => ['name' => $name],
        ]);

        return $response->toArray();
    }

    /**
     * @return array<mixed> OpenAPI representation of a Tag
     */
    public function updateTag(string $slug, Tag $tagModel): array
    {
        $jwt = $this->authContext->getJwt()
            ?? throw new \RuntimeException('JWT token not found');

        $url = self::BASE_URL . '/api/users/me/tags/' . $slug;

        $response = $this->httpClient->request('PATCH', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/merge-patch+json',
            ],
            'json' => $tagModel->toArray(),
        ]);

        return $response->toArray();
    }

    public function deleteTag(string $slug): void
    {
        $jwt = $this->authContext->getJwt()
            ?? throw new \RuntimeException('JWT token not found');

        $url = self::BASE_URL . '/api/users/me/tags/' . $slug;

        $response = $this->httpClient->request('DELETE', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/ld+json',
            ],
        ]);
    }
}
