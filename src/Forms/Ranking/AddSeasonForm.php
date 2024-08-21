<?php

namespace App\Forms\Ranking;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class AddSeasonForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id', HiddenType::class, options:[
                'attr' => [
                    'value' => 0,
                ],
            ])
            ->add('name', TextType::class)
            ->add(
                'startDate',
                DateType::class,
            )
            ->add(
                'endDate',
                DateType::class,
            )
            ->add('Add', SubmitType::class);
    }
}
