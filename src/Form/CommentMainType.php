<?php

namespace App\Form;

use App\Entity\Comment;
use App\Entity\Trick;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', TextareaType::class, [
                'required' => true, 
                'attr' => [
                    'rows' => 2, 
                    'placeholder' => 'Type your comment',
                    'class' => 'rounded p-2'
                ]
            ])
//             ->add('createdAt', null, [
//                 'widget' => 'single_text'
//             ])
//             ->add('trick', EntityType::class, [
//                 'class' => Trick::class,
// 'choice_label' => 'id',
//             ])
//             ->add('author', EntityType::class, [
//                 'class' => User::class,
// 'choice_label' => 'id',
//             ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Comment::class,
        ]);
    }
}
