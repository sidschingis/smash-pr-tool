<?php

namespace App\Forms\Event;

use App\Enum\Event\Filter;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FilterEventForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add(
                Filter::ID->value,
                TextType::class,
                options: [
                    'required' => false,
                    'empty_data' => '',
                ]
            )
            ->add(
                Filter::REGION->value,
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
