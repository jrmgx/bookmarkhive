<?php

namespace App\Service;

use App\Entity\Bookmark;
use App\Entity\FileObject;
use App\Message\BookmarkArchiveToPdfMessage;
use App\Naming\HashAndSubdirectories;
use App\Repository\BookmarkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Gotenberg\Gotenberg;
use Gotenberg\Stream;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class ArchiveToPdfConverter
{
    public function __construct(
        #[Autowire('%env(GOTENBERG_HOST)%')]
        private readonly string $gotenbergHost,
        #[Autowire('@default.storage')]
        private readonly FilesystemOperator $filesystemOperator,
        private readonly HashAndSubdirectories $hashAndSubdirectories,
        private readonly BookmarkRepository $bookmarkRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    #[AsMessageHandler]
    public function handleBookmarkArchiveToPdfMessage(BookmarkArchiveToPdfMessage $message): void
    {
        $bookmark = $this->bookmarkRepository->findOneById($message->bookmarkId)
            ->getQuery()->getOneOrNullResult()
            ?? throw new \LogicException('This bookmark does not exist.')
        ;

        $this->convertToPdf($bookmark);
    }

    /**
     * Documentation at:
     * - https://github.com/gotenberg/gotenberg-php#chromium.
     * - https://gotenberg.dev/docs/.
     */
    private function convertToPdf(Bookmark $bookmark): void
    {
        $archiveFileObject = $bookmark->archive
            ?? throw new \LogicException('This bookmark does not have an attached archive.');

        $data = gzdecode($this->filesystemOperator->read($archiveFileObject->filePath));
        if (!$data) {
            throw new \LogicException('Can not decode gz archive file.');
        }

        $request = Gotenberg::chromium($this->gotenbergHost)
            ->pdf()
            ->singlePage()
            ->paperSize('1200px', '768px')
            ->margins(0, 0, 0, 0)
            ->printBackground()
            // TODO naming file with date etc?
            ->html(Stream::string($bookmark->id . '.html', $data))
        ;

        $response = Gotenberg::send($request);
        $content = $response->getBody()->getContents();
        $name = $this->hashAndSubdirectories->name() . '.pdf';

        //        $request = Gotenberg::chromium($this->gotenbergHost)
        //            ->screenshot()
        //            ->width('1200')
        //            ->png()
        //            // TODO naming file with date etc?
        //            ->html(Stream::string($bookmark->id.'.html', $data));
        //
        //        $response = Gotenberg::send($request);
        //        $content = $response->getBody()->getContents();
        //        $name = $this->hashAndSubdirectories->name() . '.png';

        $this->filesystemOperator->write($name, $content);

        $pdfFileObject = new FileObject();
        $pdfFileObject->owner = $archiveFileObject->owner;
        $pdfFileObject->filePath = $name;
        $pdfFileObject->mime = $this->filesystemOperator->mimeType($name);
        $pdfFileObject->size = $this->filesystemOperator->fileSize($name);

        $this->entityManager->persist($pdfFileObject);

        $bookmark->pdf = $pdfFileObject;

        $this->entityManager->flush();
    }
}
