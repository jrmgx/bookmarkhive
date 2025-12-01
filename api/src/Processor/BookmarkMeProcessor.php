<?php

namespace App\Processor;

use ApiPlatform\Metadata\HttpOperation;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Entity\Bookmark;
use App\Entity\User;
use App\Helper\UrlHelper;
use App\Message\BookmarkArchiveToPdfMessage;
use App\Repository\BookmarkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProcessorInterface<Bookmark, Bookmark>
 */
final readonly class BookmarkMeProcessor implements ProcessorInterface
{
    /**
     * @param ProcessorInterface<Bookmark, Bookmark> $processor
     */
    public function __construct(
        #[Autowire('@api_platform.doctrine.orm.state.persist_processor')]
        private ProcessorInterface $processor,
        private Security $security,
        private BookmarkRepository $bookmarkRepository,
        private MessageBusInterface $messageBus,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @param Bookmark $data
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): Bookmark
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new \RuntimeException('Current user not found. Authentication required.');
        }

        // Automatically set the owner to the current logged-in user
        $data->owner = $user;

        // TODO that may be used for put operation too so it needs a condition here
        // Find previous version and outdate it
        $normalizedUrl = UrlHelper::normalize($data->url);
        if ($existingBookmark = $this->bookmarkRepository->findLastOneByOwnerAndUrl($user, $normalizedUrl)) {
            $existingBookmark->outdated = true;
        }

        if ($data->archive) {
            // This is needed because at that point, the bookmark is not yet persisted,
            // so the BookmarkArchiveToPdfMessage won't find it
            if ($operation instanceof HttpOperation && 'POST' === $operation->getMethod()) {
                $this->entityManager->persist($data);
                $this->entityManager->flush();
            }
            // TODO i guess it has to be added on the put operation or is it here too?
            $this->messageBus->dispatch(new BookmarkArchiveToPdfMessage($data->id));
        }

        return $this->processor->process($data, $operation, $uriVariables, $context);
    }
}
