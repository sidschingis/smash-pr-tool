<?php

namespace App\Forms\Event;

use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ImportSingleEventForm extends AbstractType
{
    #[Override]
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $data = $options['data'] ?? [];
        $action = $data['action'] ?? [];

        $builder->setMethod('POST');
        $builder->setAction($action);

        $builder->add(
            'singleEventId',
            TextType::class,
            [
                'required' => false,
                'label_attr' => [
                    'class' => 'row-label',
                ],
            ],
        );

        $builder->add('go', SubmitType::class, ['label' => 'Import Single Event']);
    }

    #[Override]
    public function getBlockPrefix()
    {
        return '';
    }
}
