<?php

declare(strict_types=1);

namespace App\Tests\Unit\Form;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Validation;

final class UserTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        $validator = Validation::createValidator();

        return [
            new ValidatorExtension($validator),
        ];
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'name' => 'Invité Test',
            'email' => 'invite@example.com',
        ];

        $user = new User();
        $form = $this->factory->create(UserType::class, $user);
        $form->submit($formData);

        $this->assertTrue($form->isSynchronized());
        $this->assertSame('Invité Test', $user->getName());
        $this->assertSame('invite@example.com', $user->getEmail());
    }

    public function testSubmitInvalidData(): void
    {
        $formData = [
            'name' => '',
            'email' => 'invalid-email',
        ];

        $user = new User();
        $form = $this->factory->create(UserType::class, $user);
        $form->submit($formData);

        $this->assertFalse($form->isValid());
    }
}
