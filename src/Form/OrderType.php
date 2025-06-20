<?php

namespace App\Form;

use App\Entity\Order;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Shipping Address Fields
            ->add('shipping_firstName', TextType::class, [
                'label' => 'Prénom',
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le prénom est requis']),
                    new Assert\Length(['max' => 50])
                ]
            ])
            ->add('shipping_lastName', TextType::class, [
                'label' => 'Nom',
                'mapped' => false,
                'attr' => ['class' => 'form-control'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le nom est requis']),
                    new Assert\Length(['max' => 50])
                ]
            ])
            ->add('shipping_address', TextType::class, [
                'label' => 'Adresse',
                'mapped' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Numéro et nom de rue'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'L\'adresse est requise']),
                    new Assert\Length(['max' => 255])
                ]
            ])
            ->add('shipping_addressComplement', TextType::class, [
                'label' => 'Complément d\'adresse',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Appartement, étage, bâtiment (optionnel)']
            ])
            ->add('shipping_postalCode', TextType::class, [
                'label' => 'Code postal',
                'mapped' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '1000'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le code postal est requis']),
                    new Assert\Length(['min' => 4, 'max' => 10])
                ]
            ])
            ->add('shipping_city', TextType::class, [
                'label' => 'Ville',
                'mapped' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Tunis'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'La ville est requise']),
                    new Assert\Length(['max' => 100])
                ]
            ])
            ->add('shipping_phone', TelType::class, [
                'label' => 'Téléphone',
                'mapped' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '+216 XX XXX XXX'],
                'constraints' => [
                    new Assert\NotBlank(['message' => 'Le numéro de téléphone est requis']),
                    new Assert\Length(['max' => 20])
                ]
            ])

            // Checkbox to use same address for billing
            ->add('sameAsBilling', CheckboxType::class, [
                'label' => 'Utiliser la même adresse pour la facturation',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'data' => true
            ])

            // Billing Address Fields
            ->add('billing_firstName', TextType::class, [
                'label' => 'Prénom',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('billing_lastName', TextType::class, [
                'label' => 'Nom',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control']
            ])
            ->add('billing_address', TextType::class, [
                'label' => 'Adresse',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Numéro et nom de rue']
            ])
            ->add('billing_addressComplement', TextType::class, [
                'label' => 'Complément d\'adresse',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Appartement, étage, bâtiment (optionnel)']
            ])
            ->add('billing_postalCode', TextType::class, [
                'label' => 'Code postal',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '1000']
            ])
            ->add('billing_city', TextType::class, [
                'label' => 'Ville',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => 'Tunis']
            ])
            ->add('billing_phone', TelType::class, [
                'label' => 'Téléphone',
                'mapped' => false,
                'required' => false,
                'attr' => ['class' => 'form-control', 'placeholder' => '+216 XX XXX XXX']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
