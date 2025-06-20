<?php

namespace App\Form;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class SubcategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom de la sous-catégorie',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: Montres de Sport'
                ]
            ])
            ->add('parent', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie principale',
                'placeholder' => 'Choisissez une catégorie principale...',
                'attr' => ['class' => 'form-select'],
                'query_builder' => function (CategoryRepository $repository) {
                    return $repository->createQueryBuilder('c')
                        ->where('c.isActive = :active')
                        ->andWhere('c.isMainCategory = :isMain')
                        ->setParameter('active', true)
                        ->setParameter('isMain', true)
                        ->orderBy('c.sortOrder', 'ASC')
                        ->addOrderBy('c.name', 'ASC');
                },
                'required' => true
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Description de la sous-catégorie...'
                ]
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Sous-catégorie active',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'data' => true // Default to active
            ])
            ->add('sortOrder', IntegerType::class, [
                'label' => 'Ordre de tri',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'min' => 0
                ],
                'data' => 0,
                'help' => 'Plus le nombre est petit, plus la sous-catégorie apparaîtra en premier dans sa catégorie principale'
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image de la sous-catégorie',
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/webp',
                            'image/gif',
                        ],
                        'mimeTypesMessage' => 'Veuillez uploader une image valide (JPEG, PNG, WebP, GIF)',
                    ])
                ],
                'attr' => ['class' => 'form-control', 'accept' => 'image/*']
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
