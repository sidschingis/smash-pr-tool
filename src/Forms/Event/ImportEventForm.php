<?php

namespace App\Forms\Event;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class ImportEventForm extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'] ?? [];
        $choices = $data['choices'] ?? [];
        $action = $data['action'] ?? [];

        $builder->setMethod('POST');
        $builder->setAction($action);

        $builder->add(
            'eventIds',
            ChoiceType::class,
            [
                'choices' => $choices,
                'expanded' => true,
                'multiple' => true,
                'required' => false,
                'label_attr' => [
                    'class' => 'row-label',
                ],
            ],
        );

        $builder->add('go', SubmitType::class, ['label' => 'Import Events']);
    }

    #[Override]
    public function getBlockPrefix()
    {
        return '';
    }
}
