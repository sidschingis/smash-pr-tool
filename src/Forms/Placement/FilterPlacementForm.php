<?php

namespace App\Forms\Placement;

use App\Enum\Placement\Filter;
use Override;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class FilterPlacementForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add(
                Filter::PLAYER->value,
                TextType::class,
                options: [
                    'required' => false,
                    'empty_data' => '',
                ]
            )
            ->add(
                Filter::EVENT->value,
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
