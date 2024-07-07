<?php

namespace App\Forms;

use App\Entity\Set;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SetForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $options += [
            'disabled' => true,
        ];
        $builder
            ->add('id', HiddenType::class, options: $options)
            ->add('winnerId', HiddenType::class, options: $options)
            ->add('loserId', HiddenType::class, options: $options)
            ->add('displayScore', HiddenType::class, options: $options)
            ->add('date', HiddenType::class, options: $options)
            ->add('eventName', HiddenType::class, options: $options)
            ->add('tournamentName', HiddenType::class, options: $options)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Set::class,
        ]);
    }
}
