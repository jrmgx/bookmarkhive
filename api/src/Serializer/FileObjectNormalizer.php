<?php

namespace App\Serializer;

use App\Entity\FileObject;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class FileObjectNormalizer implements NormalizerInterface
{
    private const string ALREADY_CALLED = 'MEDIA_OBJECT_NORMALIZER_ALREADY_CALLED';

    public function __construct(
        #[Autowire(service: 'api_platform.jsonld.normalizer.item')]
        private readonly NormalizerInterface $normalizer,
        private readonly string $storageDefaultPublicPath,
    ) {
    }

    /**
     * @param $data FileObject
     */
    public function normalize($data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        $context[self::ALREADY_CALLED] = true;

        // TODO use flysystem instead
        $data->contentUrl =
            $this->storageDefaultPublicPath . '/' . $data->filePath;

        return $this->normalizer->normalize($data, $format, $context);
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof FileObject;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            FileObject::class => true,
        ];
    }
}
