<?php

namespace App\Forms;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;

class TournamentForm extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'] ?? [];
        $choices = $data['choices'] ?? [];
        $action = $data['action'] ?? [];

        $builder->setMethod('GET');
        $builder->setAction($action);

        $builder->add(
            'tournamentIds',
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

        $builder->add('go', SubmitType::class, ['label' => 'Show Sets']);
    }

    #[Override]
    public function getBlockPrefix()
    {
        return '';
    }
}
