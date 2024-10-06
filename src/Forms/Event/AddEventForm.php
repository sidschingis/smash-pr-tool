<?php

namespace App\Forms\Event;

use App\Enum\Event\Field;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AddEventForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(Field::ID->value, TextType::class)
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
            ->add('Add', SubmitType::class);
    }
}
