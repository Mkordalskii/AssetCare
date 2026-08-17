<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Asset;
use App\Entity\AssetCategory;
use App\Entity\Manufacturer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class AssetType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options,
    ): void {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => [
                    'placeholder' => 'For example: Office laptop',
                ],
            ])
            ->add('category', EntityType::class, [
                'class' => AssetCategory::class,
                'choice_label' => 'name',
                'label' => 'Category',
                'placeholder' => 'Choose a category',
                'required' => true,
            ])
            ->add('manufacturer', EntityType::class, [
                'class' => Manufacturer::class,
                'choice_label' => 'name',
                'label' => 'Manufacturer',
                'placeholder' => 'No manufacturer',
                'required' => false,
            ])
            ->add('model', TextType::class, [
                'label' => 'Model',
                'required' => false,
            ])
            ->add('serialNumber', TextType::class, [
                'label' => 'Serial number',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'rows' => 4,
                ],
            ])
            ->add('purchaseDate', DateType::class, [
                'label' => 'Purchase date',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('purchasePrice', NumberType::class, [
                'label' => 'Purchase price',
                'required' => false,
                'input' => 'string',
                'scale' => 2,
                'html5' => true,
                'attr' => [
                    'min' => 0,
                    'max' => 9999999999.99,
                    'step' => 0.01,
                ],
            ])
            ->add('warrantyExpiresAt', DateType::class, [
                'label' => 'Warranty expires at',
                'required' => false,
                'widget' => 'single_text',
                'input' => 'datetime_immutable',
            ])
            ->add('save', SubmitType::class, [
                'label' => $options['submit_label'],
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Asset::class,
            'submit_label' => 'Save asset',
        ]);
        $resolver->setAllowedTypes('submit_label', 'string');
    }
}
