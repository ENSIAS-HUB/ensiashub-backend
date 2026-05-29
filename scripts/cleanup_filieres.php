<?php

// Garder uniquement les filières Tronc Commun (is_tronc_commun = true)
$aGarder = App\Models\Filiere::where('is_tronc_commun', true)->pluck('id');

dump('Filières conservées : ' . $aGarder->implode(', '));

$aSupprimer = App\Models\Filiere::whereNotIn('id', $aGarder)->get();

foreach ($aSupprimer as $filiere) {
    $moduleIds  = App\Models\Module::where('filiere_id', $filiere->id)->pluck('id');
    $elementIds = App\Models\ElementModule::whereIn('module_id', $moduleIds)->pluck('id');

    $docsSupprimes = App\Models\Document::whereIn('element_module_id', $elementIds)->forceDelete();
    App\Models\ElementModule::whereIn('module_id', $moduleIds)->delete();
    App\Models\Module::where('filiere_id', $filiere->id)->delete();
    $filiere->delete();

    dump('🗑️ Supprimé : ' . $filiere->nom . ' (' . $docsSupprimes . ' docs)');
}

dump('✅ Terminé. Filières restantes : ' . App\Models\Filiere::count());
App\Models\Filiere::all(['id','nom','slug'])->each(fn($f) => dump('  → ' . $f->nom . ' [' . $f->slug . ']'));
