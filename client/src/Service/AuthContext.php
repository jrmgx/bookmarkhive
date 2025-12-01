<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

class AuthContext
{
    private const string COOKIE_KEY_JWT = 'jwt';

    public function __construct(
        private readonly RequestStack $requestStack,
    ) {
    }

    public function getJwt(): ?string
    {
        return $this->requestStack->getMainRequest()?->cookies->get(self::COOKIE_KEY_JWT);
    }

    public function isLoggedIn(): bool
    {
        return (bool) $this->getJwt();
    }

    public function setLoggedIn(string $jwt, Response $response): Response
    {
        $cookie = Cookie::create(self::COOKIE_KEY_JWT, $jwt)
            ->withHttpOnly()
            ->withSecure()
            ->withExpires(new \DateTimeImmutable('+ 1 year'))
        ;

        $response->headers->setCookie($cookie);

        return $response;
    }
}
