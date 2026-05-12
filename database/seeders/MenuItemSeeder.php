<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;

class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        MenuItem::truncate();

        $img = [
            'cafe'       => 'https://images.unsplash.com/photo-1509042239860-f550ce710b93?w=400',
            'cafe_noir'  => 'https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?w=400',
            'lait'       => 'https://images.unsplash.com/photo-1550583724-b2692b85b150?w=400',
            'choco_lait' => 'https://images.unsplash.com/photo-1572490122747-3968b75cc699?w=400',
            'eau'        => 'https://images.unsplash.com/photo-1548839140-29a749e1cf4d?w=400',
            'soda'       => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?w=400',
            'jus_orange' => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=400',
            'jus_banane' => 'https://images.unsplash.com/photo-1587132137056-bfbf0166836e?w=400',
            'jus_pomme'  => 'https://images.unsplash.com/photo-1576506295286-5cda18df43e7?w=400',
            'jus_citron' => 'https://images.unsplash.com/photo-1621506289937-a8e4df240d0b?w=400',
            'jus_avocat' => 'https://images.unsplash.com/photo-1638176066959-9045bf1e01f4?w=400',
            'jus_mix'    => 'https://images.unsplash.com/photo-1497534446932-c925b458314e?w=400',
            'ftour'      => 'https://images.unsplash.com/photo-1504754524776-8f4f37790ca0?w=400',
            'omelette'   => 'https://images.unsplash.com/photo-1510693206972-df098062cb71?w=400',
            'msemen'     => 'https://images.unsplash.com/photo-1590301157890-4810ed352733?w=400',
            'croissant'  => 'https://images.unsplash.com/photo-1555507036-ab1f4038808a?w=400',
            'basboussa'  => 'https://images.unsplash.com/photo-1621303837174-89787a7d4729?w=400',
            'millefeuille'=> 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400',
            'crepe'      => 'https://images.unsplash.com/photo-1519676867240-f03562e64548?w=400',
            'raib'       => 'https://images.unsplash.com/photo-1628191081676-8b13ddef0b69?w=400',
            'schneck'    => 'https://images.unsplash.com/photo-1509365465985-25d11c17e812?w=400',
            'pain_choco' => 'https://images.unsplash.com/photo-1623247417659-c3bb1de8ef39?w=400',
            'salade'     => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400',
            'salade_pates'=> 'https://images.unsplash.com/photo-1473093226555-0b73d21d9b6b?w=400',
            'tajine'     => 'https://images.unsplash.com/photo-1541518763669-27fef04b14ea?w=400',
            'couscous'   => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=400',
            'poele'      => 'https://images.unsplash.com/photo-1606851091851-e8c8c0fea5ba?w=400',
            'soupe'      => 'https://images.unsplash.com/photo-1548940740-204726a19be3?w=400',
            'lentilles'  => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=400',
            'burger'     => 'https://images.unsplash.com/photo-1568901346375-23c9450c58cd?w=400',
            'pastitsio'  => 'https://images.unsplash.com/photo-1621996346565-e3dbc646d9a9?w=400',
            'pizza'      => 'https://images.unsplash.com/photo-1574071318508-1cdbab80d002?w=400',
            'panini'     => 'https://images.unsplash.com/photo-1509722747041-616f39b57569?w=400',
            'sandwich'   => 'https://images.unsplash.com/photo-1550547660-d9450f859349?w=400',
            'tacos'      => 'https://images.unsplash.com/photo-1565299585323-38d6b0865b47?w=400',
            'bocadillos' => 'https://images.unsplash.com/photo-1553909489-cd47e0907980?w=400',
        ];

        $menu = [
            // ── Boissons Chaudes ──────────────────────────────────────────────
            ['nomPlat' => 'Café crème',        'description' => 'Café au lait crémeux servi chaud',        'categorie' => 'Boissons Chaudes',      'prix' => 5,    'image_url' => $img['cafe']],
            ['nomPlat' => 'Café noir',          'description' => 'Espresso noir serré',                     'categorie' => 'Boissons Chaudes',      'prix' => 5,    'image_url' => $img['cafe_noir']],
            ['nomPlat' => 'Lait',               'description' => 'Lait chaud nature',                       'categorie' => 'Boissons Chaudes',      'prix' => 5,    'image_url' => $img['lait']],
            ['nomPlat' => 'Lait au chocolat',   'description' => 'Lait chaud au chocolat',                  'categorie' => 'Boissons Chaudes',      'prix' => 5,    'image_url' => $img['choco_lait']],

            // ── Boissons Froides ──────────────────────────────────────────────
            ['nomPlat' => 'Eau 33cl',           'description' => 'Bouteille d\'eau minérale 33cl',          'categorie' => 'Boissons Froides',      'prix' => 3,    'image_url' => $img['eau']],
            ['nomPlat' => 'Eau 50cl',           'description' => 'Bouteille d\'eau minérale 50cl',          'categorie' => 'Boissons Froides',      'prix' => 4,    'image_url' => $img['eau']],
            ['nomPlat' => 'Eau 1,5L',           'description' => 'Grande bouteille d\'eau minérale 1,5L',   'categorie' => 'Boissons Froides',      'prix' => 6,    'image_url' => $img['eau']],
            ['nomPlat' => 'Soda canette 25cl',  'description' => 'Canette de soda 25cl',                    'categorie' => 'Boissons Froides',      'prix' => 5,    'image_url' => $img['soda']],

            // ── Jus Naturels ──────────────────────────────────────────────────
            ['nomPlat' => 'Jus d\'orange',      'description' => 'Jus d\'orange pressé frais',              'categorie' => 'Jus Naturels',          'prix' => 10,   'image_url' => $img['jus_orange']],
            ['nomPlat' => 'Jus de banane',      'description' => 'Jus de banane frais',                     'categorie' => 'Jus Naturels',          'prix' => 10,   'image_url' => $img['jus_banane']],
            ['nomPlat' => 'Jus de pomme',       'description' => 'Jus de pomme frais',                      'categorie' => 'Jus Naturels',          'prix' => 10,   'image_url' => $img['jus_pomme']],
            ['nomPlat' => 'Jus de citron',      'description' => 'Jus de citron pressé',                    'categorie' => 'Jus Naturels',          'prix' => 6,    'image_url' => $img['jus_citron']],
            ['nomPlat' => 'Jus avocat',         'description' => 'Jus d\'avocat onctueux',                  'categorie' => 'Jus Naturels',          'prix' => 15,   'image_url' => $img['jus_avocat']],
            ['nomPlat' => 'Jus panaché',        'description' => 'Mélange de jus de fruits frais',          'categorie' => 'Jus Naturels',          'prix' => 15,   'image_url' => $img['jus_mix']],

            // ── Petit Déjeuner ────────────────────────────────────────────────
            ['nomPlat' => 'Ftour complet',          'description' => 'Petit déjeuner marocain : msemen, beurre, miel, confiture et thé', 'categorie' => 'Petit Dejeuner', 'prix' => 25, 'image_url' => $img['ftour']],
            ['nomPlat' => 'Omelette 2 oeufs',       'description' => 'Omelette nature à deux oeufs',                                    'categorie' => 'Petit Dejeuner', 'prix' => 8,  'image_url' => $img['omelette']],
            ['nomPlat' => 'Omelette au fromage',     'description' => 'Omelette fondante au fromage',                                    'categorie' => 'Petit Dejeuner', 'prix' => 15, 'image_url' => $img['omelette']],
            ['nomPlat' => 'Msemen complet',          'description' => 'Msemen servi avec beurre et miel',                                'categorie' => 'Petit Dejeuner', 'prix' => 4,  'image_url' => $img['msemen']],
            ['nomPlat' => 'Rghifa / Msemen simple',  'description' => 'Msemen ou rghifa nature',                                         'categorie' => 'Petit Dejeuner', 'prix' => 3,  'image_url' => $img['msemen']],
            ['nomPlat' => 'Rghifa / Msemen fromage', 'description' => 'Msemen ou rghifa garni de fromage fondu',                         'categorie' => 'Petit Dejeuner', 'prix' => 5,  'image_url' => $img['msemen']],
            ['nomPlat' => 'Rghifa / Msemen miel',    'description' => 'Msemen ou rghifa nappé de miel',                                  'categorie' => 'Petit Dejeuner', 'prix' => 7,  'image_url' => $img['msemen']],

            // ── Pâtisserie & Desserts ─────────────────────────────────────────
            ['nomPlat' => 'Croissant',      'description' => 'Croissant au beurre croustillant',              'categorie' => 'Patisserie & Desserts', 'prix' => 4,   'image_url' => $img['croissant']],
            ['nomPlat' => 'Basboussa',      'description' => 'Gâteau à la semoule et sirop de fleur d\'oranger', 'categorie' => 'Patisserie & Desserts', 'prix' => 4, 'image_url' => $img['basboussa']],
            ['nomPlat' => 'Mille-feuille',  'description' => 'Mille-feuille à la crème pâtissière',            'categorie' => 'Patisserie & Desserts', 'prix' => 4,  'image_url' => $img['millefeuille']],
            ['nomPlat' => 'Crêpe chocolat', 'description' => 'Crêpe garnie de pâte à tartiner chocolatée',     'categorie' => 'Patisserie & Desserts', 'prix' => 5,  'image_url' => $img['crepe']],
            ['nomPlat' => 'Raïb nature',    'description' => 'Yaourt marocain nature',                          'categorie' => 'Patisserie & Desserts', 'prix' => 4,  'image_url' => $img['raib']],
            ['nomPlat' => 'Raïb au sirop',  'description' => 'Yaourt marocain au sirop de fruits',              'categorie' => 'Patisserie & Desserts', 'prix' => 5,  'image_url' => $img['raib']],
            ['nomPlat' => 'Schneck',        'description' => 'Viennoiserie roulée à la cannelle et raisins',   'categorie' => 'Patisserie & Desserts', 'prix' => 4.5,'image_url' => $img['schneck']],
            ['nomPlat' => 'Petit pain choco','description' => 'Petit pain fourré au chocolat',                 'categorie' => 'Patisserie & Desserts', 'prix' => 4.5,'image_url' => $img['pain_choco']],

            // ── Salades ───────────────────────────────────────────────────────
            ['nomPlat' => 'Salade italienne',  'description' => 'Salade fraîche à l\'italienne avec mozzarella', 'categorie' => 'Salades', 'prix' => 18, 'image_url' => $img['salade']],
            ['nomPlat' => 'Salade de pâtes',   'description' => 'Salade de pâtes aux légumes et vinaigrette',   'categorie' => 'Salades', 'prix' => 15, 'image_url' => $img['salade_pates']],
            ['nomPlat' => 'Salade chef',        'description' => 'Grande salade composée du chef',               'categorie' => 'Salades', 'prix' => 22, 'image_url' => $img['salade']],

            // ── Plats & Spécialités ───────────────────────────────────────────
            ['nomPlat' => 'Tajine poulet légumes',              'description' => 'Tajine de poulet mijoté aux légumes de saison',             'categorie' => 'Plats & Specialites', 'prix' => 25, 'image_url' => $img['tajine']],
            ['nomPlat' => 'Tajine poulet frites & olives',      'description' => 'Tajine de poulet avec frites et olives marinées',           'categorie' => 'Plats & Specialites', 'prix' => 25, 'image_url' => $img['tajine']],
            ['nomPlat' => 'Tagine boeuf légumes',               'description' => 'Tagine de boeuf mijoté aux légumes',                        'categorie' => 'Plats & Specialites', 'prix' => 30, 'image_url' => $img['tajine']],
            ['nomPlat' => 'Tagine boeuf raisin sec & pruneaux', 'description' => 'Tagine de boeuf aux raisins secs et pruneaux',              'categorie' => 'Plats & Specialites', 'prix' => 30, 'image_url' => $img['tajine']],
            ['nomPlat' => 'Tagine kefta et oeuf',               'description' => 'Tagine de boulettes de viande avec oeuf poché',             'categorie' => 'Plats & Specialites', 'prix' => 25, 'image_url' => $img['tajine']],
            ['nomPlat' => 'Couscous poulet (Ven.)',              'description' => 'Couscous traditionnel au poulet — servi le vendredi',       'categorie' => 'Plats & Specialites', 'prix' => 25, 'image_url' => $img['couscous']],
            ['nomPlat' => 'Couscous viande (Ven.)',              'description' => 'Couscous traditionnel à la viande — servi le vendredi',     'categorie' => 'Plats & Specialites', 'prix' => 30, 'image_url' => $img['couscous']],
            ['nomPlat' => 'Rfisa (Mercredi)',                    'description' => 'Rfisa au poulet et lentilles — servi le mercredi',          'categorie' => 'Plats & Specialites', 'prix' => 25, 'image_url' => $img['couscous']],
            ['nomPlat' => 'Tanjia poulet',                       'description' => 'Tanjia marrakchie au poulet cuit à l\'étouffée',           'categorie' => 'Plats & Specialites', 'prix' => 30, 'image_url' => $img['tajine']],
            ['nomPlat' => 'Tanjia viande',                       'description' => 'Tanjia marrakchie à la viande cuit à l\'étouffée',         'categorie' => 'Plats & Specialites', 'prix' => 50, 'image_url' => $img['tajine']],
            ['nomPlat' => 'Émincé de poulet',                    'description' => 'Émincé de poulet en sauce, servi avec du pain',             'categorie' => 'Plats & Specialites', 'prix' => 40, 'image_url' => $img['poele']],
            ['nomPlat' => 'Kar3in',                              'description' => 'Pieds de veau mijotés aux épices',                          'categorie' => 'Plats & Specialites', 'prix' => 35, 'image_url' => $img['soupe']],
            ['nomPlat' => 'M9ila kfta',                          'description' => 'Poêle de kefta aux épices marocaines',                      'categorie' => 'Plats & Specialites', 'prix' => 20, 'image_url' => $img['poele']],
            ['nomPlat' => 'M9ila kbda',                          'description' => 'Poêle de foie de veau aux épices',                          'categorie' => 'Plats & Specialites', 'prix' => 20, 'image_url' => $img['poele']],
            ['nomPlat' => 'M9ila fruits de mer',                 'description' => 'Poêle de fruits de mer aux épices',                         'categorie' => 'Plats & Specialites', 'prix' => 25, 'image_url' => $img['poele']],
            ['nomPlat' => 'M9ila mixte',                         'description' => 'Poêle mixte : kefta, foie et fruits de mer',                'categorie' => 'Plats & Specialites', 'prix' => 30, 'image_url' => $img['poele']],
            ['nomPlat' => 'Loubia',                              'description' => 'Haricots blancs mijotés en sauce tomate',                   'categorie' => 'Plats & Specialites', 'prix' => 10, 'image_url' => $img['lentilles']],
            ['nomPlat' => 'Lentilles',                           'description' => 'Lentilles cuisinées aux épices',                            'categorie' => 'Plats & Specialites', 'prix' => 8,  'image_url' => $img['lentilles']],
            ['nomPlat' => 'Hrira',                               'description' => 'Soupe marocaine traditionnelle aux légumes et pois chiches', 'categorie' => 'Plats & Specialites', 'prix' => 10, 'image_url' => $img['soupe']],
            ['nomPlat' => 'Hrira complet',                       'description' => 'Hrira accompagnée de dattes et chebakia',                   'categorie' => 'Plats & Specialites', 'prix' => 9,  'image_url' => $img['soupe']],
            ['nomPlat' => 'Lben',                                'description' => 'Lait fermenté marocain nature',                             'categorie' => 'Plats & Specialites', 'prix' => 4,  'image_url' => $img['lait']],

            // ── Burgers & Pastitsio ───────────────────────────────────────────
            ['nomPlat' => 'Cheeseburger',           'description' => 'Burger avec steak haché et fromage fondu',       'categorie' => 'Burgers & Pastitsio', 'prix' => 22, 'image_url' => $img['burger']],
            ['nomPlat' => 'Hamburger simple + frites','description' => 'Hamburger classique servi avec frites',        'categorie' => 'Burgers & Pastitsio', 'prix' => 20, 'image_url' => $img['burger']],
            ['nomPlat' => 'Pastitsio Poulet',        'description' => 'Gratin de pâtes au poulet et béchamel',         'categorie' => 'Burgers & Pastitsio', 'prix' => 25, 'image_url' => $img['pastitsio']],
            ['nomPlat' => 'Pastitsio V.H',           'description' => 'Gratin de pâtes à la viande hachée et béchamel','categorie' => 'Burgers & Pastitsio', 'prix' => 25, 'image_url' => $img['pastitsio']],
            ['nomPlat' => 'Pastitsio Mix',           'description' => 'Gratin de pâtes mix poulet & viande hachée',    'categorie' => 'Burgers & Pastitsio', 'prix' => 28, 'image_url' => $img['pastitsio']],

            // ── Pizzas ────────────────────────────────────────────────────────
            ['nomPlat' => 'Pizza Margarita',    'description' => 'Pizza tomate, mozzarella et basilic',                 'categorie' => 'Pizzas', 'prix' => 20, 'image_url' => $img['pizza']],
            ['nomPlat' => 'Pizza Thon',         'description' => 'Pizza au thon, olives et oignons',                    'categorie' => 'Pizzas', 'prix' => 22, 'image_url' => $img['pizza']],
            ['nomPlat' => 'Pizza Végétarienne', 'description' => 'Pizza aux légumes grillés et mozzarella',             'categorie' => 'Pizzas', 'prix' => 22, 'image_url' => $img['pizza']],
            ['nomPlat' => 'Pizza Poulet',       'description' => 'Pizza au poulet grillé, poivrons et champignons',     'categorie' => 'Pizzas', 'prix' => 25, 'image_url' => $img['pizza']],
            ['nomPlat' => 'Pizza V.H',          'description' => 'Pizza à la viande hachée et légumes',                 'categorie' => 'Pizzas', 'prix' => 25, 'image_url' => $img['pizza']],

            // ── Fast-Food ─────────────────────────────────────────────────────
            ['nomPlat' => 'Bocadillos sans frites',     'description' => 'Bocadillos garni sans frites',                   'categorie' => 'Fast-Food', 'prix' => 10, 'image_url' => $img['bocadillos']],
            ['nomPlat' => 'Bocadillos avec frites',     'description' => 'Bocadillos garni avec frites',                   'categorie' => 'Fast-Food', 'prix' => 15, 'image_url' => $img['bocadillos']],
            ['nomPlat' => 'Panini thon sans frites',    'description' => 'Panini au thon et légumes, sans frites',         'categorie' => 'Fast-Food', 'prix' => 15, 'image_url' => $img['panini']],
            ['nomPlat' => 'Panini thon avec frites',    'description' => 'Panini au thon et légumes, avec frites',         'categorie' => 'Fast-Food', 'prix' => 22, 'image_url' => $img['panini']],
            ['nomPlat' => 'Panini poulet sans frites',  'description' => 'Panini au poulet grillé, sans frites',           'categorie' => 'Fast-Food', 'prix' => 20, 'image_url' => $img['panini']],
            ['nomPlat' => 'Panini poulet avec frites',  'description' => 'Panini au poulet grillé, avec frites',           'categorie' => 'Fast-Food', 'prix' => 25, 'image_url' => $img['panini']],
            ['nomPlat' => 'Panini V.H sans frites',     'description' => 'Panini à la viande hachée, sans frites',         'categorie' => 'Fast-Food', 'prix' => 20, 'image_url' => $img['panini']],
            ['nomPlat' => 'Panini V.H avec frites',     'description' => 'Panini à la viande hachée, avec frites',         'categorie' => 'Fast-Food', 'prix' => 25, 'image_url' => $img['panini']],
            ['nomPlat' => 'Sandwich poulet sans frites','description' => 'Sandwich au poulet, sans frites',                'categorie' => 'Fast-Food', 'prix' => 15, 'image_url' => $img['sandwich']],
            ['nomPlat' => 'Sandwich poulet avec frites','description' => 'Sandwich au poulet, avec frites',                'categorie' => 'Fast-Food', 'prix' => 20, 'image_url' => $img['sandwich']],
            ['nomPlat' => 'Sandwich V.H sans frites',   'description' => 'Sandwich à la viande hachée, sans frites',       'categorie' => 'Fast-Food', 'prix' => 15, 'image_url' => $img['sandwich']],
            ['nomPlat' => 'Sandwich V.H avec frites',   'description' => 'Sandwich à la viande hachée, avec frites',       'categorie' => 'Fast-Food', 'prix' => 20, 'image_url' => $img['sandwich']],
            ['nomPlat' => 'Sandwich Mix sans frites',   'description' => 'Sandwich mixte poulet & viande hachée, sans frites','categorie' => 'Fast-Food', 'prix' => 20, 'image_url' => $img['sandwich']],
            ['nomPlat' => 'Sandwich Mix avec frites',   'description' => 'Sandwich mixte poulet & viande hachée, avec frites','categorie' => 'Fast-Food', 'prix' => 25, 'image_url' => $img['sandwich']],
            ['nomPlat' => 'Tacos poulet sans frites',   'description' => 'Tacos au poulet et sauce fromagère, sans frites', 'categorie' => 'Fast-Food', 'prix' => 22, 'image_url' => $img['tacos']],
            ['nomPlat' => 'Tacos poulet avec frites',   'description' => 'Tacos au poulet et sauce fromagère, avec frites', 'categorie' => 'Fast-Food', 'prix' => 27, 'image_url' => $img['tacos']],
            ['nomPlat' => 'Tacos V.H sans frites',      'description' => 'Tacos à la viande hachée et sauce, sans frites',  'categorie' => 'Fast-Food', 'prix' => 22, 'image_url' => $img['tacos']],
            ['nomPlat' => 'Tacos V.H avec frites',      'description' => 'Tacos à la viande hachée et sauce, avec frites',  'categorie' => 'Fast-Food', 'prix' => 27, 'image_url' => $img['tacos']],
            ['nomPlat' => 'Tacos mix sans frites',      'description' => 'Tacos mix poulet & viande hachée, sans frites',   'categorie' => 'Fast-Food', 'prix' => 25, 'image_url' => $img['tacos']],
            ['nomPlat' => 'Tacos mix avec frites',      'description' => 'Tacos mix poulet & viande hachée, avec frites',   'categorie' => 'Fast-Food', 'prix' => 30, 'image_url' => $img['tacos']],
        ];

        foreach ($menu as $item) {
            MenuItem::create([
                'nomPlat'      => $item['nomPlat'],
                'description'  => $item['description'],
                'image_url'    => $item['image_url'],
                'categorie'    => $item['categorie'],
                'estDisponible'=> true,
                'prix'         => $item['prix'],
            ]);
        }
    }
}
