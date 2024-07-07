<?php

namespace App\Forms;

use App\Entity\Set;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
            ]
        );

        $builder->add('go', SubmitType::class, ['label' => 'Show Sets']);
    }

    #[Override]
    public function getBlockPrefix()
    {
        return '';
    }
}
