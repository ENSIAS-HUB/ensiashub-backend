<?php
App\Models\Filiere::all(['id','nom','slug','is_tronc_commun'])
    ->each(fn($f) => dump($f->id . ' — ' . $f->nom . ' [slug:' . $f->slug . '] [tc:' . ($f->is_tronc_commun ? 'oui' : 'non') . ']'));
