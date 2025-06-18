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
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ProductType extends AbstractType
{
    private ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
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
            ->add('category', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'label' => 'Catégorie',
                'placeholder' => 'Choisissez une catégorie...',
                'attr' => ['class' => 'form-select'],
                'query_builder' => function (CategoryRepository $repository) {
                    return $repository->createQueryBuilder('c')
                        ->where('c.isActive = :active')
                        ->setParameter('active', true)
                        ->orderBy('c.name', 'ASC');
                },
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
