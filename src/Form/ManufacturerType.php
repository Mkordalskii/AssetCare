<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\Manufacturer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ManufacturerType extends AbstractType
{
    public function buildForm(
        FormBuilderInterface $builder,
        array $options
    ): void {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
                'attr' => [
                    'placeholder' => 'For example: Bosch',
                ],
            ])
            ->add('website', UrlType::class, [
                'label' => 'Website',
                'required' => false,
                'attr' => [
                    'placeholder' => 'https://example.com',
                ],
            ])
            ->add('supportEmail', EmailType::class, [
                'label' => 'Support email',
                'required' => false,
                'attr' => [
                    'placeholder' => 'support@example.com',
                ],
            ])
            ->add('supportPhone', TelType::class, [
                'label' => 'Support phone',
                'required' => false,
                'attr' => [
                    'placeholder' => '+49 123 456789',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save manufacturer',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Manufacturer::class,
        ]);
    }
}