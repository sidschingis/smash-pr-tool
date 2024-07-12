<?php

namespace App\Forms\Player;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FilterPlayerForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add(
                'idFilter',
                TextType::class,
                options: [
                    'required' => false,
                    'empty_data' => '',
                ]
            )
            ->add(
                'tagFilter',
                TextType::class,
                options: [
                    'required' => false,
                    'empty_data' => '',
                ]
            )
            ->add('filter', SubmitType::class);
    }

    #[Override]
    public function getBlockPrefix()
    {
        return '';
    }
}
