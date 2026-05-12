<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            FiliereSeeder::class,
            UserSeeder::class,
            AllowedEmailSeeder::class,
            GroupSeeder::class,
            AdhesionGroupSeeder::class,
            PublicationSeeder::class,
            PublicationReviewSeeder::class,
            CommentaireSeeder::class,
            ReactionSeeder::class,
            InteractionSeeder::class,
            ModuleSeeder::class,
            DocumentSeeder::class,
            DocumentReviewSeeder::class,
            MenuItemSeeder::class,
        ]);
    }
}
