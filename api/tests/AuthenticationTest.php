<?php

namespace App\Tests;

use App\Entity\User;
use Hautelook\AliceBundle\PhpUnit\ReloadDatabaseTrait;

class AuthenticationTest extends BaseApiTestCase
{
    use ReloadDatabaseTrait;

    public function testLogin(): void
    {
        $container = $this->container;

        $user = new User();
        $user->email = 'test@example.com';
        $user->username = 'test';
        $user->setPassword(
            $container->get('security.user_password_hasher')->hashPassword($user, 'test')
        );

        $manager = $container->get('doctrine')->getManager();
        $manager->persist($user);
        $manager->flush();

        // retrieve a token
        $this->request('POST', '/auth', [
            'headers' => ['Content-Type' => 'application/json'],
            'json' => [
                'email' => 'test@example.com',
                'password' => 'test',
            ],
        ]);

        $json = $this->dump($this->getResponseArray());
        $this->assertResponseIsSuccessful();
        $this->assertArrayHasKey('token', $json);

        // test not authorized
        $this->request('GET', '/users/me/bookmarks');
        $this->assertResponseStatusCodeSame(401);

        // test authorized
        $this->request('GET', '/users/me/bookmarks', [
            'auth_bearer' => $json['token'],
        ]);
        $this->assertResponseIsSuccessful();
    }
}
