<?php

namespace App\DataFixtures;

use App\Entity\Category;
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

        // Créer les 4 catégories principales
        $categories = [];

        // 1. HOMME - Catégorie principale
        $hommeCategory = new Category();
        $hommeCategory->setName('Homme');
        $hommeCategory->setSlug('homme');
        $hommeCategory->setDescription('Collection de montres pour hommes - Élégance et sophistication masculine');
        $hommeCategory->setIsMainCategory(true);
        $hommeCategory->setMainCategoryType('homme');
        $hommeCategory->setSortOrder(1);
        $manager->persist($hommeCategory);
        $categories['homme'] = $hommeCategory;

        // 2. FEMME - Catégorie principale
        $femmeCategory = new Category();
        $femmeCategory->setName('Femme');
        $femmeCategory->setSlug('femme');
        $femmeCategory->setDescription('Collection de montres pour femmes - Raffinement et élégance féminine');
        $femmeCategory->setIsMainCategory(true);
        $femmeCategory->setMainCategoryType('femme');
        $femmeCategory->setSortOrder(2);
        $manager->persist($femmeCategory);
        $categories['femme'] = $femmeCategory;

        // 3. ENFANT - Catégorie principale
        $enfantCategory = new Category();
        $enfantCategory->setName('Enfant');
        $enfantCategory->setSlug('enfant');
        $enfantCategory->setDescription('Collection de montres pour enfants - Colorées, ludiques et résistantes');
        $enfantCategory->setIsMainCategory(true);
        $enfantCategory->setMainCategoryType('enfant');
        $enfantCategory->setSortOrder(3);
        $manager->persist($enfantCategory);
        $categories['enfant'] = $enfantCategory;

        // 4. ACCESSOIRES - Catégorie principale
        $accessoiresCategory = new Category();
        $accessoiresCategory->setName('Accessoires');
        $accessoiresCategory->setSlug('accessoires');
        $accessoiresCategory->setDescription('Accessoires horlogers - Bracelets, boîtiers, outils et plus');
        $accessoiresCategory->setIsMainCategory(true);
        $accessoiresCategory->setMainCategoryType('accessoires');
        $accessoiresCategory->setSortOrder(4);
        $manager->persist($accessoiresCategory);
        $categories['accessoires'] = $accessoiresCategory;

        // Sous-catégories pour HOMME
        $luxuryWatches = new Category();
        $luxuryWatches->setName('Montres de Luxe');
        $luxuryWatches->setSlug('montres-de-luxe');
        $luxuryWatches->setDescription('Collection exclusive de montres de prestige des plus grandes manufactures horlogères');
        $luxuryWatches->setParent($hommeCategory);
        $luxuryWatches->setSortOrder(1);
        $manager->persist($luxuryWatches);
        $categories['luxury'] = $luxuryWatches;

        $sportWatches = new Category();
        $sportWatches->setName('Montres Sport');
        $sportWatches->setSlug('montres-sport');
        $sportWatches->setDescription('Montres robustes conçues pour les activités sportives et l\'aventure');
        $sportWatches->setParent($hommeCategory);
        $sportWatches->setSortOrder(2);
        $manager->persist($sportWatches);
        $categories['sport'] = $sportWatches;

        $dressWatches = new Category();
        $dressWatches->setName('Montres Habillées');
        $dressWatches->setSlug('montres-habillees');
        $dressWatches->setDescription('Montres élégantes pour les occasions formelles et le quotidien raffiné');
        $dressWatches->setParent($hommeCategory);
        $dressWatches->setSortOrder(3);
        $manager->persist($dressWatches);
        $categories['dress'] = $dressWatches;

        $pilotWatches = new Category();
        $pilotWatches->setName('Montres d\'Aviateur');
        $pilotWatches->setSlug('montres-aviateur');
        $pilotWatches->setDescription('Montres inspirées de l\'aviation avec fonctions de navigation');
        $pilotWatches->setParent($hommeCategory);
        $pilotWatches->setSortOrder(4);
        $manager->persist($pilotWatches);
        $categories['pilot'] = $pilotWatches;

        // Sous-catégories pour FEMME
        $femmeElegante = new Category();
        $femmeElegante->setName('Montres Élégantes');
        $femmeElegante->setSlug('montres-elegantes-femme');
        $femmeElegante->setDescription('Montres raffinées pour femmes élégantes');
        $femmeElegante->setParent($femmeCategory);
        $femmeElegante->setSortOrder(1);
        $manager->persist($femmeElegante);
        $categories['femme_elegante'] = $femmeElegante;

        $femmeSport = new Category();
        $femmeSport->setName('Montres Sport Femme');
        $femmeSport->setSlug('montres-sport-femme');
        $femmeSport->setDescription('Montres sportives adaptées aux femmes actives');
        $femmeSport->setParent($femmeCategory);
        $femmeSport->setSortOrder(2);
        $manager->persist($femmeSport);
        $categories['femme_sport'] = $femmeSport;

        $femmeBijoux = new Category();
        $femmeBijoux->setName('Montres Bijoux');
        $femmeBijoux->setSlug('montres-bijoux');
        $femmeBijoux->setDescription('Montres-bijoux ornées de pierres précieuses');
        $femmeBijoux->setParent($femmeCategory);
        $femmeBijoux->setSortOrder(3);
        $manager->persist($femmeBijoux);
        $categories['femme_bijoux'] = $femmeBijoux;

        // Sous-catégories pour ENFANT
        $enfantGarcon = new Category();
        $enfantGarcon->setName('Montres Garçon');
        $enfantGarcon->setSlug('montres-garcon');
        $enfantGarcon->setDescription('Montres spécialement conçues pour les garçons');
        $enfantGarcon->setParent($enfantCategory);
        $enfantGarcon->setSortOrder(1);
        $manager->persist($enfantGarcon);
        $categories['enfant_garcon'] = $enfantGarcon;

        $enfantFille = new Category();
        $enfantFille->setName('Montres Fille');
        $enfantFille->setSlug('montres-fille');
        $enfantFille->setDescription('Montres spécialement conçues pour les filles');
        $enfantFille->setParent($enfantCategory);
        $enfantFille->setSortOrder(2);
        $manager->persist($enfantFille);
        $categories['enfant_fille'] = $enfantFille;

        $enfantEducative = new Category();
        $enfantEducative->setName('Montres Éducatives');
        $enfantEducative->setSlug('montres-educatives');
        $enfantEducative->setDescription('Montres pour apprendre à lire l\'heure');
        $enfantEducative->setParent($enfantCategory);
        $enfantEducative->setSortOrder(3);
        $manager->persist($enfantEducative);
        $categories['enfant_educative'] = $enfantEducative;

        // Sous-catégories pour ACCESSOIRES
        $bracelets = new Category();
        $bracelets->setName('Bracelets');
        $bracelets->setSlug('bracelets');
        $bracelets->setDescription('Bracelets de montres en cuir, métal et autres matériaux');
        $bracelets->setParent($accessoiresCategory);
        $bracelets->setSortOrder(1);
        $manager->persist($bracelets);
        $categories['bracelets'] = $bracelets;

        $boitiers = new Category();
        $boitiers->setName('Boîtiers et Écrins');
        $boitiers->setSlug('boitiers-ecrins');
        $boitiers->setDescription('Boîtiers de rangement et écrins pour montres');
        $boitiers->setParent($accessoiresCategory);
        $boitiers->setSortOrder(2);
        $manager->persist($boitiers);
        $categories['boitiers'] = $boitiers;

        $outils = new Category();
        $outils->setName('Outils d\'Entretien');
        $outils->setSlug('outils-entretien');
        $outils->setDescription('Outils pour l\'entretien et la réparation de montres');
        $outils->setParent($accessoiresCategory);
        $outils->setSortOrder(3);
        $manager->persist($outils);
        $categories['outils'] = $outils;

        // Sous-sous-catégories (comme avant)
        $mechanical = new Category();
        $mechanical->setName('Mécaniques');
        $mechanical->setSlug('mecaniques');
        $mechanical->setDescription('Montres à mouvement mécanique manuel ou automatique');
        $mechanical->setParent($luxuryWatches);
        $mechanical->setSortOrder(1);
        $manager->persist($mechanical);
        $categories['mechanical'] = $mechanical;

        $chronographs = new Category();
        $chronographs->setName('Chronographes');
        $chronographs->setSlug('chronographes');
        $chronographs->setDescription('Montres équipées de fonction chronométrage');
        $chronographs->setParent($sportWatches);
        $chronographs->setSortOrder(1);
        $manager->persist($chronographs);
        $categories['chronographs'] = $chronographs;

        $diving = new Category();
        $diving->setName('Plongée');
        $diving->setSlug('plongee');
        $diving->setDescription('Montres étanches conçues pour la plongée sous-marine');
        $diving->setParent($sportWatches);
        $diving->setSortOrder(2);
        $manager->persist($diving);
        $categories['diving'] = $diving;

        $complications = new Category();
        $complications->setName('Grandes Complications');
        $complications->setSlug('grandes-complications');
        $complications->setDescription('Montres avec complications horlogères avancées');
        $complications->setParent($luxuryWatches);
        $complications->setSortOrder(2);
        $manager->persist($complications);
        $categories['complications'] = $complications;

        // CRÉER BEAUCOUP PLUS DE PRODUITS POUR TOUTES LES CATÉGORIES

        $products = [
            // === MONTRES HOMME - LUXE ===
            [
                'name' => 'Rolex Submariner',
                'description' => 'La Rolex Submariner est une montre de plongée iconique, reconnue pour sa robustesse et son élégance intemporelle.',
                'price' => '8500.00',
                'brand' => 'Rolex',
                'model' => 'Submariner 116610LN',
                'stock' => 5,
                'image' => 'rolex-submariner.jpg',
                'category' => $categories['diving'],
            ],
            [
                'name' => 'Patek Philippe Calatrava',
                'description' => 'La Patek Philippe Calatrava incarne l\'élégance horlogère dans sa forme la plus pure.',
                'price' => '25000.00',
                'brand' => 'Patek Philippe',
                'model' => 'Calatrava 5196P',
                'stock' => 2,
                'image' => 'patek-philippe-calatrava.jpg',
                'category' => $categories['dress'],
            ],
            [
                'name' => 'Audemars Piguet Royal Oak',
                'description' => 'L\'Audemars Piguet Royal Oak révolutionna l\'horlogerie de luxe en 1972.',
                'price' => '18500.00',
                'brand' => 'Audemars Piguet',
                'model' => 'Royal Oak 15400ST',
                'stock' => 4,
                'image' => 'audemars-piguet-royal-oak.jpg',
                'category' => $categories['luxury'],
            ],
            [
                'name' => 'Vacheron Constantin Patrimony',
                'description' => 'Montre ultra-plate d\'exception, symbole de l\'horlogerie de prestige.',
                'price' => '22000.00',
                'brand' => 'Vacheron Constantin',
                'model' => 'Patrimony Ultra-Thin',
                'stock' => 3,
                'image' => 'vacheron-patrimony.jpg',
                'category' => $categories['mechanical'],
            ],
            [
                'name' => 'Jaeger-LeCoultre Reverso',
                'description' => 'Montre iconique à boîtier réversible, créée en 1931 pour les joueurs de polo.',
                'price' => '12500.00',
                'brand' => 'Jaeger-LeCoultre',
                'model' => 'Reverso Classic',
                'stock' => 6,
                'image' => 'jlc-reverso.jpg',
                'category' => $categories['dress'],
            ],

            // === MONTRES HOMME - SPORT ===
            [
                'name' => 'Omega Speedmaster',
                'description' => 'L\'Omega Speedmaster Professional, surnommée "Moonwatch", première montre portée sur la Lune.',
                'price' => '6200.00',
                'brand' => 'Omega',
                'model' => 'Speedmaster Professional',
                'stock' => 8,
                'image' => 'omega-speedmaster.jpg',
                'category' => $categories['chronographs'],
            ],
            [
                'name' => 'TAG Heuer Carrera',
                'description' => 'Inspirée par l\'univers de la course automobile, la TAG Heuer Carrera allie élégance sportive et précision.',
                'price' => '3800.00',
                'brand' => 'TAG Heuer',
                'model' => 'Carrera Calibre 16',
                'stock' => 12,
                'image' => 'tag-heuer-carrera.jpg',
                'category' => $categories['chronographs'],
            ],
            [
                'name' => 'Seiko Prospex Diver',
                'description' => 'Montre de plongée professionnelle, étanche à 200m avec lunette unidirectionnelle.',
                'price' => '450.00',
                'brand' => 'Seiko',
                'model' => 'Prospex SRPD46K1',
                'stock' => 15,
                'image' => 'seiko-prospex.jpg',
                'category' => $categories['diving'],
            ],
            [
                'name' => 'Casio G-Shock',
                'description' => 'Montre ultra-résistante aux chocs, parfaite pour les sports extrêmes.',
                'price' => '180.00',
                'brand' => 'Casio',
                'model' => 'G-Shock GA-2100',
                'stock' => 25,
                'image' => 'casio-gshock.jpg',
                'category' => $categories['sport'],
            ],
            [
                'name' => 'Tudor Black Bay',
                'description' => 'Montre de plongée vintage inspirée des modèles Tudor des années 1950.',
                'price' => '2800.00',
                'brand' => 'Tudor',
                'model' => 'Black Bay 58',
                'stock' => 10,
                'image' => 'tudor-blackbay.jpg',
                'category' => $categories['diving'],
            ],

            // === MONTRES HOMME - AVIATEUR ===
            [
                'name' => 'Breitling Navitimer',
                'description' => 'La Breitling Navitimer est l\'instrument de référence des pilotes depuis 1952.',
                'price' => '7200.00',
                'brand' => 'Breitling',
                'model' => 'Navitimer B01',
                'stock' => 3,
                'image' => 'breitling-navitimer.jpg',
                'category' => $categories['pilot'],
            ],
            [
                'name' => 'IWC Pilot\'s Watch',
                'description' => 'Montre d\'aviateur classique avec grande couronne et cadran lisible.',
                'price' => '4500.00',
                'brand' => 'IWC',
                'model' => 'Pilot\'s Watch Mark XVIII',
                'stock' => 7,
                'image' => 'iwc-pilot.jpg',
                'category' => $categories['pilot'],
            ],
            [
                'name' => 'Citizen Eco-Drive Pilot',
                'description' => 'Montre solaire d\'aviateur avec réserve de marche de 6 mois.',
                'price' => '320.00',
                'brand' => 'Citizen',
                'model' => 'Eco-Drive BM8180',
                'stock' => 18,
                'image' => 'citizen-pilot.jpg',
                'category' => $categories['pilot'],
            ],

            // === MONTRES FEMME - ÉLÉGANTES ===
            [
                'name' => 'Cartier Tank',
                'description' => 'Icône de l\'horlogerie féminine, la Tank de Cartier séduit par son design rectangulaire intemporel.',
                'price' => '3200.00',
                'brand' => 'Cartier',
                'model' => 'Tank Solo',
                'stock' => 8,
                'image' => 'cartier-tank.jpg',
                'category' => $categories['femme_elegante'],
            ],
            [
                'name' => 'Chanel J12',
                'description' => 'Montre en céramique haute technologie, symbole de modernité et d\'élégance.',
                'price' => '4800.00',
                'brand' => 'Chanel',
                'model' => 'J12 White Ceramic',
                'stock' => 5,
                'image' => 'chanel-j12.jpg',
                'category' => $categories['femme_elegante'],
            ],
            [
                'name' => 'Omega Constellation',
                'description' => 'Montre féminine élégante avec cadran nacré et index diamants.',
                'price' => '2400.00',
                'brand' => 'Omega',
                'model' => 'Constellation Manhattan',
                'stock' => 12,
                'image' => 'omega-constellation.jpg',
                'category' => $categories['femme_elegante'],
            ],
            [
                'name' => 'Longines DolceVita',
                'description' => 'Montre rectangulaire féminine alliant tradition horlogère et design contemporain.',
                'price' => '1200.00',
                'brand' => 'Longines',
                'model' => 'DolceVita L5.255.4',
                'stock' => 15,
                'image' => 'longines-dolcevita.jpg',
                'category' => $categories['femme_elegante'],
            ],

            // === MONTRES FEMME - BIJOUX ===
            [
                'name' => 'Rolex Lady-Datejust',
                'description' => 'Montre féminine de prestige avec lunette sertie de diamants.',
                'price' => '12000.00',
                'brand' => 'Rolex',
                'model' => 'Lady-Datejust 279384RBR',
                'stock' => 3,
                'image' => 'rolex-lady-datejust.jpg',
                'category' => $categories['femme_bijoux'],
            ],
            [
                'name' => 'Bulgari Serpenti',
                'description' => 'Montre-bracelet iconique en forme de serpent, symbole de séduction et de mystère.',
                'price' => '8500.00',
                'brand' => 'Bulgari',
                'model' => 'Serpenti Tubogas',
                'stock' => 4,
                'image' => 'bulgari-serpenti.jpg',
                'category' => $categories['femme_bijoux'],
            ],
            [
                'name' => 'Van Cleef & Arpels Alhambra',
                'description' => 'Montre précieuse ornée du motif trèfle emblématique de la maison.',
                'price' => '15000.00',
                'brand' => 'Van Cleef & Arpels',
                'model' => 'Alhambra VCARO3GM00',
                'stock' => 2,
                'image' => 'vcarpels-alhambra.jpg',
                'category' => $categories['femme_bijoux'],
            ],

            // === MONTRES FEMME - SPORT ===
            [
                'name' => 'TAG Heuer Formula 1 Lady',
                'description' => 'Montre sportive féminine inspirée de la Formule 1.',
                'price' => '1800.00',
                'brand' => 'TAG Heuer',
                'model' => 'Formula 1 Lady WAH1212',
                'stock' => 10,
                'image' => 'tag-formula1-lady.jpg',
                'category' => $categories['femme_sport'],
            ],
            [
                'name' => 'Garmin Venu 2S',
                'description' => 'Montre connectée GPS avec suivi santé et fonctions sportives avancées.',
                'price' => '450.00',
                'brand' => 'Garmin',
                'model' => 'Venu 2S',
                'stock' => 20,
                'image' => 'garmin-venu2s.jpg',
                'category' => $categories['femme_sport'],
            ],

            // === MONTRES ENFANT - GARÇON ===
            [
                'name' => 'Flik Flak Spider-Man',
                'description' => 'Montre éducative pour garçon avec le héros Spider-Man.',
                'price' => '45.00',
                'brand' => 'Flik Flak',
                'model' => 'Spider-Man FLSP012',
                'stock' => 30,
                'image' => 'flikflak-spiderman.jpg',
                'category' => $categories['enfant_garcon'],
            ],
            [
                'name' => 'Casio G-Shock Mini',
                'description' => 'Version junior de la célèbre G-Shock, résistante aux chocs.',
                'price' => '85.00',
                'brand' => 'Casio',
                'model' => 'G-Shock Mini GMN-691',
                'stock' => 25,
                'image' => 'casio-gshock-mini.jpg',
                'category' => $categories['enfant_garcon'],
            ],
            [
                'name' => 'Timex Kids Analog',
                'description' => 'Montre analogique colorée pour apprendre à lire l\'heure.',
                'price' => '35.00',
                'brand' => 'Timex',
                'model' => 'Kids Analog TW7C13400',
                'stock' => 40,
                'image' => 'timex-kids-analog.jpg',
                'category' => $categories['enfant_educative'],
            ],

            // === MONTRES ENFANT - FILLE ===
            [
                'name' => 'Flik Flak Disney Princess',
                'description' => 'Montre éducative pour fille avec les princesses Disney.',
                'price' => '48.00',
                'brand' => 'Flik Flak',
                'model' => 'Disney Princess FLNP028',
                'stock' => 35,
                'image' => 'flikflak-princess.jpg',
                'category' => $categories['enfant_fille'],
            ],
            [
                'name' => 'Ice-Watch ICE fantasia',
                'description' => 'Montre colorée et fun pour les petites filles.',
                'price' => '55.00',
                'brand' => 'Ice-Watch',
                'model' => 'ICE fantasia 016722',
                'stock' => 28,
                'image' => 'icewatch-fantasia.jpg',
                'category' => $categories['enfant_fille'],
            ],
            [
                'name' => 'Hello Kitty Kids Watch',
                'description' => 'Montre éducative avec le personnage Hello Kitty.',
                'price' => '32.00',
                'brand' => 'Hello Kitty',
                'model' => 'HK25921',
                'stock' => 45,
                'image' => 'hellokitty-watch.jpg',
                'category' => $categories['enfant_educative'],
            ],

            // === ACCESSOIRES - BRACELETS ===
            [
                'name' => 'Bracelet Cuir Alligator',
                'description' => 'Bracelet en cuir d\'alligator véritable, fait main.',
                'price' => '180.00',
                'brand' => 'Hirsch',
                'model' => 'Alligator 18mm',
                'stock' => 50,
                'image' => 'bracelet-alligator.jpg',
                'category' => $categories['bracelets'],
            ],
            [
                'name' => 'Bracelet Acier Inoxydable',
                'description' => 'Bracelet en acier inoxydable 316L avec fermoir déployant.',
                'price' => '120.00',
                'brand' => 'Staib',
                'model' => 'Mesh Steel 20mm',
                'stock' => 35,
                'image' => 'bracelet-steel.jpg',
                'category' => $categories['bracelets'],
            ],
            [
                'name' => 'Bracelet NATO Nylon',
                'description' => 'Bracelet NATO en nylon résistant, disponible en plusieurs couleurs.',
                'price' => '25.00',
                'brand' => 'Crown & Buckle',
                'model' => 'NATO Supreme 22mm',
                'stock' => 100,
                'image' => 'bracelet-nato.jpg',
                'category' => $categories['bracelets'],
            ],

            // === ACCESSOIRES - BOÎTIERS ===
            [
                'name' => 'Boîtier 12 Montres Luxe',
                'description' => 'Boîtier de rangement en bois laqué pour 12 montres avec coussins en velours.',
                'price' => '250.00',
                'brand' => 'Rothenschild',
                'model' => 'RS-2029-12E',
                'stock' => 15,
                'image' => 'boitier-12-montres.jpg',
                'category' => $categories['boitiers'],
            ],
            [
                'name' => 'Écrin Voyage 4 Montres',
                'description' => 'Étui de voyage compact en cuir pour 4 montres.',
                'price' => '85.00',
                'brand' => 'Wolf',
                'model' => 'Travel Case 458406',
                'stock' => 25,
                'image' => 'ecrin-voyage.jpg',
                'category' => $categories['boitiers'],
            ],

            // === ACCESSOIRES - OUTILS ===
            [
                'name' => 'Kit Outils Horloger',
                'description' => 'Kit complet d\'outils pour l\'entretien et la réparation de montres.',
                'price' => '120.00',
                'brand' => 'Bergeon',
                'model' => 'Tool Kit 7812',
                'stock' => 20,
                'image' => 'kit-outils.jpg',
                'category' => $categories['outils'],
            ],
            [
                'name' => 'Démonte-Bracelet Professionnel',
                'description' => 'Outil professionnel pour retirer les maillons de bracelet.',
                'price' => '45.00',
                'brand' => 'Bergeon',
                'model' => 'Link Remover 6767',
                'stock' => 30,
                'image' => 'demonte-bracelet.jpg',
                'category' => $categories['outils'],
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
            $product->setCategory($productData['category']);

            $manager->persist($product);
        }

        $manager->flush();
    }
}
