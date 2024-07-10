<?php

namespace App\Forms;

use App\ControllerData\EventData;
use App\Entity\Set;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventForm extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'] ?? [];
        /** @var EventData */
        $eventData = $data['eventData'];
        $action = $data['action'] ?? [];

        $builder->setMethod('GET');
        $builder->setAction($action);

        $builder->add(
            'eventId',
            HiddenType::class,
            [
                'attr' => [
                    'value' => $eventData->eventId
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
