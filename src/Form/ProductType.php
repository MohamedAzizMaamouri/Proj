<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Product;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    private ProductRepository $productRepository;
    private CategoryRepository $categoryRepository;

    public function __construct(ProductRepository $productRepository, CategoryRepository $categoryRepository)
    {
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // Get existing brands
        $existingBrands = $this->productRepository->findAllBrands();
        $brandChoices = array_combine($existingBrands, $existingBrands);

        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du produit',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Rolex Submariner']
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 5, 'placeholder' => 'Description détaillée du produit...']
            ])
            ->add('price', MoneyType::class, [
                'label' => 'Prix (DT)',
                'currency' => 'TND',
                'attr' => ['class' => 'form-control', 'placeholder' => '0.00']
            ])
            ->add('brandChoice', ChoiceType::class, [
                'label' => 'Marque',
                'mapped' => false,
                'choices' => array_merge(['Nouvelle marque' => 'new'], $brandChoices),
                'attr' => ['class' => 'form-select', 'onchange' => 'toggleBrandInput(this.value)'],
                'placeholder' => 'Choisissez une marque...'
            ])
            ->add('brand', TextType::class, [
                'label' => 'Nouvelle marque',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom de la nouvelle marque',
                    'style' => 'display: none;'
                ]
            ])
            ->add('model', TextType::class, [
                'label' => 'Modèle',
                'attr' => ['class' => 'form-control', 'placeholder' => 'Ex: Submariner 116610LN']
            ])
            ->add('mainCategory', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie principale',
                'placeholder' => 'Choisissez une catégorie principale...',
                'attr' => ['class' => 'form-select', 'onchange' => 'updateSubcategories(this.value)'],
                'mapped' => false,
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
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Sous-catégorie',
                'placeholder' => 'Choisissez d\'abord une catégorie principale...',
                'attr' => ['class' => 'form-select'],
                'choices' => [], // Will be populated dynamically
                'required' => false
            ])
            ->add('stock', IntegerType::class, [
                'label' => 'Stock',
                'attr' => ['class' => 'form-control', 'min' => 0, 'placeholder' => '0']
            ])
            ->add('isActive', CheckboxType::class, [
                'label' => 'Produit actif',
                'required' => false,
                'attr' => ['class' => 'form-check-input'],
                'data' => true // Default to active
            ])
            ->add('imageFile', FileType::class, [
                'label' => 'Image du produit',
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

        // Handle dynamic subcategory loading
        $formModifier = function (FormInterface $form, Category $mainCategory = null) {
            $subcategories = null === $mainCategory ? [] : $mainCategory->getActiveChildren();

            $form->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Sous-catégorie',
                'placeholder' => $mainCategory ? 'Choisissez une sous-catégorie...' : 'Choisissez d\'abord une catégorie principale...',
                'attr' => ['class' => 'form-select'],
                'choices' => $subcategories,
                'required' => false
            ]);
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                $product = $event->getData();
                $mainCategory = null;

                if ($product && $product->getCategory()) {
                    if ($product->getCategory()->isMainCategory()) {
                        $mainCategory = $product->getCategory();
                    } else {
                        $mainCategory = $product->getCategory()->getParent();
                    }
                }

                $formModifier($event->getForm(), $mainCategory);

                // Set the main category field
                if ($mainCategory) {
                    $event->getForm()->get('mainCategory')->setData($mainCategory);
                }
            }
        );

        $builder->get('mainCategory')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $mainCategory = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $mainCategory);
            }
        );

        // Handle brand selection logic
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['brandChoice'])) {
                if ($data['brandChoice'] === 'new') {
                    // If "new" is selected, use the custom brand field
                    $form->get('brand')->setData($data['brand'] ?? '');
                } else {
                    // If existing brand is selected, use that value
                    $form->get('brand')->setData($data['brandChoice']);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
