<?php
$m = App\Models\Module::find('019e5183-ba10-7331-8e8d-c06062e284f3');
if ($m) {
    // Pas d'éléments ni de docs — suppression directe
    $m->delete();
    dump('✅ Supprimé : ' . $m->nom);
} else {
    dump('❓ Non trouvé');
}

// Vérification : seul l_IA reste
$remaining = App\Models\Module::where('nom', 'LIKE', '%Introduction%IA%')->get(['id','nom']);
$remaining->each(fn($r) => dump('Restant : ' . $r->nom . ' | ' . $r->id));
