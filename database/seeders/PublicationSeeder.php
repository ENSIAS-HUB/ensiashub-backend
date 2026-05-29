<?php

namespace Database\Seeders;

use App\Models\Publication;
use App\Models\Group;
use App\Models\User;
use Illuminate\Database\Seeder;

class PublicationSeeder extends Seeder
{
    public function run(): void
    {
        $users  = User::all();
        $groups = Group::all();

        if ($users->isEmpty()) {
            $this->command->warn('Pas d\'utilisateurs — PublicationSeeder ignoré.');
            return;
        }

        $contents = [
            'Bienvenue à tous les étudiants ! N\'hésitez pas à poser vos questions ici.',
            'Quelqu\'un a des ressources pour le module Algorithmique Avancée ?',
            'Tournoi de League of Legends ce vendredi à 18h ! Inscrivez-vous ici.',
            'Nouveau cours de Réseaux disponible dans le drive !',
            'Bienvenue à tous les nouveaux étudiants !',
            'Réunion du bureau ce jeudi à 17h en salle B204.',
            'Les résultats du concours de programmation sont disponibles !',
            'N\'oubliez pas de rendre vos projets avant vendredi.',
        ];

        foreach ($contents as $i => $contenu) {
            $group = $groups->isNotEmpty() ? $groups->random() : null;

            Publication::create([
                'contenu'          => $contenu,
                'typeMedia'        => null,
                'statutValidation' => 'Valide',
                'user_id'          => $users->random()->id,
                'groupe_id'        => $group?->id,
                'visibility'       => 'global',
                'publishedAt'      => now()->subHours($i),
            ]);
        }
    }
}