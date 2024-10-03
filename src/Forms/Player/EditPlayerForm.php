<?php

namespace App\Forms\Player;

use App\Enum\Player\Field;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class EditPlayerForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                Field::ID->value,
                TextType::class,
                options: [
                    'attr' => [
                        'readonly' => true,
                    ],
                ],
            )
            ->add(Field::TAG->value, TextType::class)
            ->add(
                Field::TWITTER->value,
                TextType::class,
                options: [
                    'required' => false,
                    'empty_data' => '',
                ]
            )
            ->add(
                Field::REGION->value,
                TextType::class,
                options: [
                    'required' => false,
                    'empty_data' => '',
                ]
            )
            ->add('edit', SubmitType::class)
            ->add('delete', SubmitType::class);
    }
}
