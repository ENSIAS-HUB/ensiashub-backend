<?php
// Recréer le module "Introduction à l'IA" (apostrophe) en S2 Tronc Commun
$module = App\Models\Module::create([
    'filiere_id' => '019e1d21-c948-7033-a443-94269381b0cb', // Tronc Commun
    'annee_id'   => '019e4c42-4e26-726d-b948-df8991bfcc9f', // 1A
    'nom'        => "Introduction à l'IA",
    'slug'       => null,
    'semestre'   => 'S2',
    'annee'      => null,
    'is_active'  => true,
]);

dump('✅ Module recréé : ' . $module->nom . ' | id=' . $module->id);

// État actuel des deux variantes
$apostrophe = App\Models\Module::where('nom', "Introduction à l'IA")->first();
$underscore  = App\Models\Module::where('nom', 'Introduction à l_IA')->first();
dump('Apostrophe : ' . ($apostrophe ? '✅ ' . $apostrophe->id : '❌ absent'));
dump('Underscore : ' . ($underscore  ? '✅ ' . $underscore->id  : '❌ absent'));
