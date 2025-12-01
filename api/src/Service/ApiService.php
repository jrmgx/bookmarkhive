<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiService
{
    private const string BASE_URL = 'http://127.0.0.1'; // TODO make env

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
     * @return array<mixed> OpenAPI representation of a Bookmark
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
            ],
        ]);

        return $response->toArray()['member'];
    }

    /**
     * @return array<mixed> OpenAPI representation of a Tag
     */
    public function getTags(): array
    {
        $jwt = $this->authContext->getJwt()
            ?? throw new \RuntimeException('JWT token not found');

        $url = self::BASE_URL . '/api/users/me/tags';

        $response = $this->httpClient->request('GET', $url, [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
            ],
        ]);

        return $response->toArray()['member'];
    }
}
