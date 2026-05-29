<?php

$s1_a_supprimer = [
    'Algorithmique & Structure de donnée',
    'Algorithmique & Structures de données',
    'Anglais technique',
    'Base de données',
    'Mathématique',
    'Programmation orienté objet',
    'Réseaux',
    "Système d'exploitation",
];

$s2_a_supprimer = [
    "Introduction à l'IA",
];

// Supprimer S1
foreach ($s1_a_supprimer as $nom) {
    $module = App\Models\Module::where('nom', 'LIKE', '%' . $nom . '%')
                               ->where('semestre', 'S1')
                               ->first();
    if ($module) {
        $elementIds = $module->elementModules()->pluck('id');
        App\Models\Document::whereIn('element_module_id', $elementIds)->forceDelete();
        $module->elementModules()->delete();
        $nomSaved = $module->nom;
        $module->delete();
        dump('✅ Supprimé S1 : ' . $nomSaved);
    } else {
        dump('❓ Non trouvé S1 : ' . $nom);
    }
}

// Supprimer S2
foreach ($s2_a_supprimer as $nom) {
    $module = App\Models\Module::where('nom', 'LIKE', '%' . $nom . '%')
                               ->where('semestre', 'S2')
                               ->first();
    if ($module) {
        $elementIds = $module->elementModules()->pluck('id');
        App\Models\Document::whereIn('element_module_id', $elementIds)->forceDelete();
        $module->elementModules()->delete();
        $nomSaved = $module->nom;
        $module->delete();
        dump('✅ Supprimé S2 : ' . $nomSaved);
    } else {
        dump('❓ Non trouvé S2 : ' . $nom);
    }
}

dump('✅ Nettoyage terminé.');
