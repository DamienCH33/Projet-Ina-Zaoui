<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Media;
use App\Entity\Album;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

final class MediaTest extends TestCase
{
    public function testSetAndGetTitle(): void
    {
        $media = new Media();
        $media->setTitle('Mon Media');
        $this->assertSame('Mon Media', $media->getTitle());
    }

    public function testSetAndGetPath(): void
    {
        $media = new Media();
        $media->setPath('/uploads/file.jpg');
        $this->assertSame('/uploads/file.jpg', $media->getPath());
    }

    public function testSetAndGetAlbum(): void
    {
        $album = new Album();
        $media = new Media();
        $media->setAlbum($album);
        $this->assertSame($album, $media->getAlbum());
    }

    public function testSetAndGetFile(): void
    {
        $media = new Media();

        $tmpFile = tempnam(sys_get_temp_dir(), 'upload');
        $uploadedFile = new UploadedFile(
            $tmpFile,
            'file.jpg',
            'image/jpeg',
            null,
            true
        );

        $media->setFile($uploadedFile);
        $this->assertSame($uploadedFile, $media->getFile());

        unlink($tmpFile);
    }
}
