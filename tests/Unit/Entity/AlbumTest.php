<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Album;
use PHPUnit\Framework\TestCase;

final class AlbumTest extends TestCase
{
    public function testSetAndGetName(): void
    {
        $album = new Album();
        $album->setName('Mon Album');
        $this->assertSame('Mon Album', $album->getName());
    }
    public function testMediasCollectionIsInitialized(): void
    {
        $album = new Album();
        $this->assertCount(0, $album->getMedias(), 'La collection de médias doit être vide au départ.');
    }
}
