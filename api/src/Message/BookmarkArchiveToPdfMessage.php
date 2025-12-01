<?php

namespace App\Message;

use App\Service\ArchiveToPdfConverter;
use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage(transport: 'async')]
readonly class BookmarkArchiveToPdfMessage
{
    /**
     * @see ArchiveToPdfConverter
     */
    public function __construct(
        public private(set) string $bookmarkId,
    ) {
    }
}
