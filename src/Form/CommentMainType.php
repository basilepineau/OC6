<?php

namespace App\Form;

use App\Entity\CommentMain;
use App\Entity\Trick;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentMainType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('text', TextareaType::class, [
                'label' => 'Votre commentaire',
                'required' => true, 
                'attr' => [
                    'rows' => 2, 
                    'placeholder' => 'Type your comment'
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
            'data_class' => CommentMain::class,
        ]);
    }
}
