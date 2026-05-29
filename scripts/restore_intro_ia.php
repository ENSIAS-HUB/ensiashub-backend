<?php
// Recréer le module "Introduction à l_IA" en S2 Tronc Commun
$module = App\Models\Module::create([
    'filiere_id' => '019e1d21-c948-7033-a443-94269381b0cb', // Tronc Commun
    'annee_id'   => '019e4c42-4e26-726d-b948-df8991bfcc9f', // 1A
    'nom'        => 'Introduction à l_IA',
    'slug'       => null,
    'semestre'   => 'S2',
    'annee'      => null,
    'is_active'  => true,
]);

dump('✅ Module recréé : ' . $module->nom . ' | id=' . $module->id);

// Vérification
$check = App\Models\Module::find($module->id);
dump('Vérification : ' . ($check ? 'OK — ' . $check->nom : 'ERREUR'));
