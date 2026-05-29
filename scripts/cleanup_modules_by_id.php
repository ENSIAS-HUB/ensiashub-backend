<?php

$ids_to_delete = [
    // S1 - cas non trouvés par LIKE
    '019e4c42-4f0a-70f3-a734-99953e5d2aa1', // Anglais Technique
    '019e4c42-4efa-7130-ac09-475ff380e177', // Bases de Données
    '019e4c42-4ec4-722c-8252-2b5a9981b9ab', // Programmation Orientée Objet
    '019e4c42-4ed6-72ff-bcce-8a201cbee1fa', // Systèmes d'Exploitation
    // S2 - variantes d'Introduction à l'IA avec apostrophe/espace différent
    '019e503c-b726-7063-aafe-84d3c491b392', // Introduction à l IA
    '019e50ad-3dee-7070-b6fd-20f1e1c5e85f', // Introduction à l_IA
];

foreach ($ids_to_delete as $id) {
    $m = App\Models\Module::find($id);
    if ($m) {
        $elIds = $m->elementModules()->pluck('id');
        App\Models\Document::whereIn('element_module_id', $elIds)->forceDelete();
        $m->elementModules()->delete();
        $nom = $m->nom;
        $sem = $m->semestre;
        $m->delete();
        dump("✅ Supprimé [{$sem}] : {$nom}");
    } else {
        dump("❓ Non trouvé : {$id}");
    }
}

dump('✅ Nettoyage terminé.');
