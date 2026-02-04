<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\Media;
use App\Form\MediaType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

final class MediaTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();
        return [new ValidatorExtension($validator)];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'title' => 'Media Test',
        ];

        $media = new Media();
        $form = $this->factory->create(MediaType::class, $media);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('Media Test', $media->getTitle());
    }

    public function testSubmitInvalidData(): void
    {
        $media = new Media();
        $form = $this->factory->create(MediaType::class, $media);
        $form->submit([
            'title' => '',
        ]);

        $this->assertFalse($form->isValid(), 'Le formulaire doit être invalide si le titre est vide.');
    }
}
