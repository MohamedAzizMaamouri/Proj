<?php

namespace App\DataFixtures;

use App\Entity\Product;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // Créer un utilisateur admin
        $admin = new User();
        $admin->setEmail('admin2@watchstore.fr');
        $admin->setFirstName('Admin');
        $admin->setLastName('WatchStore');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // Créer un utilisateur normal
        $user = new User();
        $user->setEmail('user@watchstore.fr');
        $user->setFirstName('Jean');
        $user->setLastName('Dupont');
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $manager->persist($user);

        // Créer des produits avec des images
        $products = [
            [
                'name' => 'Rolex Submariner',
                'description' => 'La Rolex Submariner est une montre de plongée iconique, reconnue pour sa robustesse et son élégance intemporelle. Dotée d\'un boîtier en acier inoxydable 904L et d\'un mouvement automatique perpétuel.',
                'price' => '8500.00',
                'brand' => 'Rolex',
                'model' => 'Submariner 116610LN',
                'stock' => 5,
                'image' => 'rolex-submariner.jpg',
            ],
            [
                'name' => 'Omega Speedmaster',
                'description' => 'L\'Omega Speedmaster Professional, surnommée "Moonwatch", est la première montre portée sur la Lune. Ce chronographe manuel emblématique combine héritage spatial et excellence horlogère suisse.',
                'price' => '6200.00',
                'brand' => 'Omega',
                'model' => 'Speedmaster Professional',
                'stock' => 8,
                'image' => 'omega-speedmaster.jpg',
            ],
            [
                'name' => 'TAG Heuer Carrera',
                'description' => 'Inspirée par l\'univers de la course automobile, la TAG Heuer Carrera allie élégance sportive et précision chronométrique. Son design épuré et ses performances techniques en font un choix d\'excellence.',
                'price' => '3800.00',
                'brand' => 'TAG Heuer',
                'model' => 'Carrera Calibre 16',
                'stock' => 12,
                'image' => 'tag-heuer-carrera.jpg',
            ],
            [
                'name' => 'Breitling Navitimer',
                'description' => 'La Breitling Navitimer est l\'instrument de référence des pilotes depuis 1952. Équipée d\'une règle à calcul circulaire, elle permet d\'effectuer tous les calculs de navigation aérienne.',
                'price' => '7200.00',
                'brand' => 'Breitling',
                'model' => 'Navitimer B01',
                'stock' => 3,
                'image' => 'breitling-navitimer.jpg',
            ],
            [
                'name' => 'Patek Philippe Calatrava',
                'description' => 'La Patek Philippe Calatrava incarne l\'élégance horlogère dans sa forme la plus pure. Cette montre dress watch présente un design intemporel et un mouvement mécanique d\'exception.',
                'price' => '25000.00',
                'brand' => 'Patek Philippe',
                'model' => 'Calatrava 5196P',
                'stock' => 2,
                'image' => 'patek-philippe-calatrava.jpg',
            ],
            [
                'name' => 'Audemars Piguet Royal Oak',
                'description' => 'L\'Audemars Piguet Royal Oak révolutionna l\'horlogerie de luxe en 1972. Son boîtier octogonal en acier et son bracelet intégré en font une icône du design horloger contemporain.',
                'price' => '18500.00',
                'brand' => 'Audemars Piguet',
                'model' => 'Royal Oak 15400ST',
                'stock' => 4,
                'image' => 'audemars-piguet-royal-oak.jpg',
            ],
        ];

        foreach ($products as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setDescription($productData['description']);
            $product->setPrice($productData['price']);
            $product->setBrand($productData['brand']);
            $product->setModel($productData['model']);
            $product->setStock($productData['stock']);
            $product->setImage($productData['image']);
            $product->setIsActive(true);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
