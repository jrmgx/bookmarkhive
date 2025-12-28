<?php

namespace App\Controller;

use App\Config\RouteAction;
use App\Config\RouteType;
use App\Entity\FileObject;
use App\Entity\User;
use App\Naming\HashAndSubdirectories;
use App\Response\JsonResponseBuilder;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route(path: '/users/me/files', name: RouteType::MeFileObjects->value)]
final class MeFileObjectController extends AbstractController
{
    public function __construct(
        private readonly HashAndSubdirectories $hashAndSubdirectories,
        #[Autowire('@default.storage')]
        private readonly FilesystemOperator $filesystemOperator,
        private readonly EntityManagerInterface $entityManager,
        private readonly JsonResponseBuilder $jsonResponseBuilder,
    ) {
    }

    /**
     * Placeholder route for iri generation.
     */
    #[Route(path: '/{id}', name: RouteAction::Get->value, methods: ['GET'])]
    public function get(): JsonResponse
    {
        throw new MethodNotAllowedHttpException(['POST']);
    }

    #[Route(path: '', name: RouteAction::Create->value, methods: ['POST'])]
    public function create(
        #[CurrentUser] User $user,
        Request $request,
    ): JsonResponse {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        $name = $this->hashAndSubdirectories->name();
        $ext = $file->guessExtension() ?? 'bin';
        $filePath = $name . '.' . $ext;

        $this->filesystemOperator->write($filePath, $file->getContent());

        $fileObject = new FileObject();
        $fileObject->owner = $user;
        $fileObject->size = (int) $file->getSize();
        $fileObject->mime = $file->getMimeType() ?? 'application/octet-stream';
        $fileObject->filePath = $filePath;

        $this->entityManager->persist($fileObject);
        $this->entityManager->flush();

        return $this->jsonResponseBuilder->single($fileObject, ['file_object:read']);
    }
}
