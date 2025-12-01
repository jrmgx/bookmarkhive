<?php

namespace App\Processor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\FileObject;
use App\Entity\User;
use App\Naming\HashAndSubdirectories;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

/**
 * @implements ProcessorInterface<FileObject, FileObject>
 */
// TODO similar to BookmarkMeProcessor merge?
final readonly class FileObjectMeProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<FileObject, FileObject> $processor
     */
    public function __construct(
        #[Autowire('@api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $processor,
        #[Autowire('@default.storage')]
        private FilesystemOperator $filesystemOperator,
        private HashAndSubdirectories $hashAndSubdirectories,
        private Security $security,
    ) {
    }

    /**
     * @param FileObject $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): FileObject
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new \RuntimeException('Current user not found. Authentication required.');
        }

        /** @var Request $request */
        $request = $context['request'];

        /** @var UploadedFile $file */
        $file = $request->files->get('file');

        $name = $this->hashAndSubdirectories->name();
        $ext = $file->guessExtension() ?? 'bin';
        $filePath = $name . '.' . $ext;

        $this->filesystemOperator->write($filePath, $file->getContent());

        $data->owner = $user;
        $data->size = (int) $file->getSize();
        $data->mime = $file->getMimeType() ?? 'application/octet-stream';
        $data->filePath = $filePath;

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
