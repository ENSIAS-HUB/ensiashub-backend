<?php
// Trouver filiere_id de Tronc Commun (principal)
$tcFiliere = App\Models\Filiere::where('slug', 'tronc-commun')->first();
dump('Tronc Commun id: ' . ($tcFiliere ? $tcFiliere->id : 'NON TROUVÉ'));

// Récupérer annee_id pour la 1ère année (TC est 1A/2A)
// Voir les annees disponibles
App\Models\Annee::all(['id','nom','niveau'])->each(fn($a) => dump($a->id.' — '.$a->nom.' | '.$a->niveau));
