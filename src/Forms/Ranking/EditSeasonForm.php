<?php

namespace App\Forms\Ranking;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class EditSeasonForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'id',
                TextType::class,
                options: [
                    'attr' => [
                        'readonly' => true,
                    ],
                ],
            )
            ->add('name', TextType::class)
            ->add(
                'startDate',
                DateType::class,
            )
            ->add(
                'endDate',
                DateType::class,
            )
            ->add('edit', SubmitType::class)
            ->add('delete', SubmitType::class);
    }
}
