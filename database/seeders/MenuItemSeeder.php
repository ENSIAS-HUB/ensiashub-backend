<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MenuItem;


class MenuItemSeeder extends Seeder
{
    public function run(): void
    {
        $menu = [
            ['nomPlat' => 'Café crème', 'categorie' => 'Boissons Chaudes', 'prix' => 5],
            ['nomPlat' => 'Café noir', 'categorie' => 'Boissons Chaudes', 'prix' => 6],
            ['nomPlat' => 'Eau 33cl', 'categorie' => 'Boissons Froides', 'prix' => 3],
            ['nomPlat' => 'Jus d\'orange', 'categorie' => 'Jus Naturels', 'prix' => 10],
            ['nomPlat' => 'Jus avocat', 'categorie' => 'Jus Naturels', 'prix' => 15],
            
            ['nomPlat' => 'Ftour complet', 'categorie' => 'Petit Dejeuner', 'prix' => 25],
            ['nomPlat' => 'Msemen complet', 'categorie' => 'Petit Dejeuner', 'prix' => 4],
            ['nomPlat' => 'Croissant', 'categorie' => 'Patisserie & Desserts', 'prix' => 4],
            ['nomPlat' => 'Mille-feuille', 'categorie' => 'Patisserie & Desserts', 'prix' => 4],

            ['nomPlat' => 'Pizza Margarita', 'categorie' => 'Pizzas', 'prix' => 20],
            ['nomPlat' => 'Pizza Poulet', 'categorie' => 'Pizzas', 'prix' => 25],
            ['nomPlat' => 'Tajine poulet légumes', 'categorie' => 'Plats & Specialites', 'prix' => 25],
            ['nomPlat' => 'Tagine boeuf raisin sec & pruneaux', 'categorie' => 'Plats & Specialites', 'prix' => 30],
            ['nomPlat' => 'Tacos mix avec frites', 'categorie' => 'Fast-Food', 'prix' => 30],
            ['nomPlat' => 'Panini poulet avec frites', 'categorie' => 'Fast-Food', 'prix' => 25],
        ];

        foreach ($menu as $item) {
            MenuItem::create([
                'nomPlat' => $item['nomPlat'],
                'categorie' => $item['categorie'],
                'estDisponible' => true,
                'prix' => $item['prix'],
            ]);
        }
    }
}