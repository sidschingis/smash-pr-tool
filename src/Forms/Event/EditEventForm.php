<?php

namespace App\Forms\Event;

use App\Enum\Event\Field;
use App\Enum\Event\Tier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class EditEventForm extends AbstractType
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
        ->add(
            Field::DATE->value,
            DateType::class,
            options: [
                'attr' => [
                    'readonly' => true,
                ],
            ],
        )
        ->add(Field::EVENT_NAME->value, TextType::class)
        ->add(Field::TOURNAMENT_NAME->value, TextType::class)
        ->add(
            Field::ENTRANTS->value,
            TextType::class,
            options: [
                'required' => false,
                'empty_data' => '0',
            ]
        )
        ->add(
            Field::NOTABLES->value,
            TextType::class,
            options: [
                'required' => false,
                'empty_data' => '0',
            ]
        )
        ->add(
            Field::TIER->value,
            EnumType::class,
            options: [
                'required' => false,
                'class' => Tier::class,
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
        ->add('Edit', SubmitType::class)
        ->add('Delete', SubmitType::class);
    }
}
