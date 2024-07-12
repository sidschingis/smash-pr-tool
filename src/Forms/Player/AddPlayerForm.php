<?php

namespace App\Forms\Player;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AddPlayerForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', TextType::class)
            ->add('tag', TextType::class)
            ->add(
                'twitterTag',
                TextType::class,
                options: [
                    'required' => false,
                    'empty_data' => '',
                ]
            )
            ->add('Add', SubmitType::class);
    }
}
