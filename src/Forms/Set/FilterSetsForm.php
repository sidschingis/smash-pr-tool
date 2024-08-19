<?php

namespace App\Forms\Set;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class FilterSetsForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add(
                'minDate',
                DateType::class,
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
