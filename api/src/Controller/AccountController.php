<?php

namespace App\Controller;

use App\Config\RouteAction;
use App\Config\RouteType;
use App\Entity\User;
use App\Response\JsonResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use OpenApi\Attributes as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/account', name: RouteType::Account->value)]
final class AccountController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly JsonResponseBuilder $jsonResponseBuilder,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[OA\Post(
        path: '/account',
        tags: ['Account'],
        operationId: 'createAccount',
        summary: 'Register a new user account',
        description: 'Creates a new user account with username and password. Returns the created user object.',
        requestBody: new OA\RequestBody(
            required: true,
            description: 'User registration data',
            content: new OA\JsonContent(
                ref: '#/components/schemas/UserCreate',
                type: 'object',
                required: ['username', 'password'],
                properties: [
                    new OA\Property(property: 'username', type: 'string', minLength: 3, maxLength: 32, description: 'Unique username'),
                    new OA\Property(property: 'password', type: 'string', minLength: 8, description: 'User password'),
                    new OA\Property(property: 'isPublic', type: 'boolean', description: 'Whether the profile is public', default: false),
                    new OA\Property(property: 'meta', type: 'object', description: 'Additional metadata as key-value pairs', additionalProperties: true),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'User account created successfully',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/UserOwner',
                    examples: [
                        new OA\Examples(
                            example: 'success',
                            value: [
                                'username' => 'johndoe',
                                'isPublic' => false,
                                'meta' => [],
                                '@iri' => 'https://bookmarkhive.test/users/me',
                            ],
                            summary: 'Successfully created user account'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error - username already exists or invalid data',
                content: new OA\JsonContent(
                    ref: '#/components/schemas/ErrorResponse',
                    examples: [
                        new OA\Examples(
                            example: 'duplicate_username',
                            value: ['error' => 'Username already exists'],
                            summary: 'Username already taken'
                        ),
                        new OA\Examples(
                            example: 'invalid_data',
                            value: ['error' => 'Username must be between 3 and 32 characters'],
                            summary: 'Validation error'
                        ),
                    ]
                )
            ),
        ]
    )]
    #[Route(path: '', name: RouteAction::Create->value, methods: ['POST'])]
    public function create(
        #[MapRequestPayload(
            serializationContext: ['groups' => ['user:create']],
            validationGroups: ['Default', 'user:create'],
        )]
        User $user,
    ): JsonResponse {
        /** @var string $plainPassword asserted by validator */
        $plainPassword = $user->getPlainPassword();
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $plainPassword)
        );
        $user->setPlainPassword(null);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->jsonResponseBuilder->single($user, ['user:owner']);
    }
}
