<?php

namespace App\Serializer;

use App\Entity\FileObject;
use App\Entity\Tag;
use App\Entity\User;
use App\Repository\FileObjectRepository;
use App\Repository\TagRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

readonly class IriDenormalizer implements DenormalizerInterface
{
    private const string PATH_TAGS = '/api/users/me/tags/';
    private const string PATH_FILE_OBJECTS = '/api/users/me/files/';

    public function __construct(
        private Security $security,
        private TagRepository $tagRepository,
        private FileObjectRepository $fileObjectRepository,
    ) {
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        /** @var User $user */
        $user = $this->security->getUser() ?? throw new \LogicException('No user logged.');

        if (Tag::class === $type) {
            $slug = substr($data, \strlen(self::PATH_TAGS));

            return $this->tagRepository->findOneByOwnerAndSlug($user, $slug, onlyPublic: false)
                ->getQuery()->getOneOrNullResult()
                ?? throw new UnprocessableEntityHttpException('This Tag does not exist.')
            ;
        }

        if (FileObject::class === $type) {
            $id = substr($data, \strlen(self::PATH_FILE_OBJECTS));

            return $this->fileObjectRepository->findOneByOwnerAndId($user, $id)
                ->getQuery()->getOneOrNullResult()
                ?? throw new UnprocessableEntityHttpException('This FileObject does not exist.')
            ;
        }

        return $data;
    }

    public function supportsDenormalization(
        mixed $data,
        string $type,
        ?string $format = null,
        array $context = [],
    ): bool {
        return
            (Tag::class === $type || FileObject::class === $type)
            && \is_string($data)
            && (str_starts_with($data, self::PATH_TAGS) || str_starts_with($data, self::PATH_FILE_OBJECTS));
    }

    /**
     * @see https://symfony.com/doc/current/serializer/custom_normalizer.html#improving-performance-of-normalizers-denormalizers
     *
     * @return array<mixed>
     */
    public function getSupportedTypes(?string $format): array
    {
        return [
            Tag::class => true,
            FileObject::class => true,
        ];
    }
}
