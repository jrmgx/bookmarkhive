<?php

namespace App\Tests\ActivityPub\Builder;

use App\ActivityPub\Builder\NoteObjectBuilder;
use App\ActivityPub\Dto\NoteObject;
use App\Entity\Bookmark;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class NoteObjectBuilderTest extends KernelTestCase
{
    public function testParseToBookmark(): void
    {
        self::bootKernel();
        $container = self::getContainer();

        /** @var NoteObjectBuilder $noteObjectBuilder */
        $noteObjectBuilder = $container->get(NoteObjectBuilder::class);

        $noteObject = new NoteObject();
        $noteObject->attachment = []; // TODO add test on that
        $noteObject->tags = []; // TODO add test on that
        $noteObject->content = <<<'HTML'
            <p>
                Cursor Blog: Scaling Agents 
                <a href="https://cursor.com/blog/scaling-agents" target="_blank" rel="nofollow noopener noreferrer">
                    <span class="invisible">https://</span>
                    <span class="">cursor.com/blog/scaling-agents</span>
                </a>
                <a href="https://api2.bookmarkhive.test/profile/bob/bookmarks?tags=writing" target="_blank" rel="nofollow noopener noreferrer tag" class="mention hashtag">
                    #<span>writing</span>
                </a>
            </p>
            HTML;

        $bookmark = $noteObjectBuilder->parseToBookmark($noteObject);

        $this->assertInstanceOf(Bookmark::class, $bookmark);
        $this->assertEquals('https://cursor.com/blog/scaling-agents', $bookmark->url);
        $this->assertEquals('Cursor Blog: Scaling Agents', $bookmark->title);
    }
}
