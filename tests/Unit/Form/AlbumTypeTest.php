<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\Album;
use App\Form\AlbumType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

final class AlbumTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();

        return [new ValidatorExtension($validator)];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'name' => 'Album Test',
        ];

        $album = new Album();
        $form = $this->factory->create(AlbumType::class, $album);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('Album Test', $album->getName());
    }

    public function testSubmitInvalidData(): void
    {
        $formData = [
            'name' => '',
        ];

        $album = new Album();
        $form = $this->factory->create(AlbumType::class, $album);
        $form->submit($formData);

        $this->assertFalse($form->isValid());
    }
}
