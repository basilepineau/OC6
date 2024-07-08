<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class ResetPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'attr' => ['autofocus' => true],
                ],
                'constraints' => [
                    new NotBlank(),
                    new Length(['min' => 6, 'max' => 4096]),
                ],
                'invalid_message' => 'The password fields must match.',
            ]);

    }

    public function validatePasswordMatches($value, ExecutionContextInterface $context)
    {
        $formData = $context->getRoot()->getData();

        if ($formData['plainPassword'] !== $value) {
            $context->buildViolation('The password fields must match.')
                ->atPath('confirmPassword')
                ->addViolation();
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            // Configure your form options here
        ]);
    }
}
