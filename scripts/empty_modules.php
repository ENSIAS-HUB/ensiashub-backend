<?php
App\Models\Module::doesntHave('elementModules')
    ->get(['id','nom','semestre'])
    ->each(fn($m) => dump($m->nom . ' [' . $m->semestre . '] id=' . $m->id));
