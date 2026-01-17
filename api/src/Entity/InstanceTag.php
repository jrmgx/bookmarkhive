<?php

namespace App\Entity;

use App\Repository\InstanceTagRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Constraints as Assert;

#[Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::ATOM])]
#[ORM\Entity(repositoryClass: InstanceTagRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_slug', fields: ['slug'])]
class InstanceTag // TODO add test on this
{
    #[Ignore]
    #[ORM\Id, ORM\Column(type: 'uuid')]
    public private(set) string $id;

    #[Assert\Length(max: 32)]
    #[ORM\Column(length: 32)]
    public string $name {
        set {
            $this->name = $value;
            $this->slug = self::slugger($value);
        }
    }

    #[Assert\NotBlank]
    #[Assert\Length(max: 32)]
    #[ORM\Column(length: 32)]
    public private(set) string $slug;

    public function __construct()
    {
        $this->id = Uuid::v7()->toString();
    }

    public static function slugger(string $name): string
    {
        $slugger = new AsciiSlugger('en');

        return mb_strtolower($slugger->slug($name));
    }
}
