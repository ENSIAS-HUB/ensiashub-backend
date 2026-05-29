<?php
// Vérifier s'il reste des modules Introduction IA
$modules = App\Models\Module::where('nom', 'LIKE', '%Introduction%IA%')
    ->orWhere('nom', 'LIKE', '%Introduction%l%IA%')
    ->get(['id','nom','semestre','filiere_id']);

if ($modules->isEmpty()) {
    dump('✅ Aucun module "Introduction à l\'IA" restant en base.');
} else {
    $modules->each(fn($m) => dump([
        'id'       => $m->id,
        'nom'      => $m->nom,
        'semestre' => $m->semestre,
        'filiere_id' => $m->filiere_id,
        'hex'      => bin2hex($m->nom),
    ]));
}
