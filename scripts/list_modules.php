<?php
App\Models\Module::whereIn('semestre', ['S1','S2'])
    ->orderBy('semestre')
    ->orderBy('nom')
    ->get(['id','nom','semestre','filiere_id'])
    ->each(fn($m) => dump('[' . $m->semestre . '] ' . $m->id . ' — ' . $m->nom));
