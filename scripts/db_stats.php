<?php
// Statistiques globales
$totalModules   = App\Models\Module::count();
$totalElements  = App\Models\ElementModule::count();
$totalDocuments = App\Models\Document::count();

dump("=== STATISTIQUES DB ===");
dump("Modules     : {$totalModules}");
dump("Éléments    : {$totalElements}");
dump("Documents   : {$totalDocuments}");

// Modules qui ont des éléments
$modulesAvecElements = App\Models\Module::has('elementModules')->count();
$modulesSansElements = App\Models\Module::doesntHave('elementModules')->count();
dump("Modules avec éléments   : {$modulesAvecElements}");
dump("Modules sans éléments   : {$modulesSansElements}");

// Quelques modules avec docs pour vérifier
dump("=== MODULES AVEC DOCS ===");
App\Models\Module::has('elementModules')->with('elementModules')->get()->take(5)->each(function($m) {
    $docsCount = App\Models\Document::whereIn('element_module_id', $m->elementModules->pluck('id'))->count();
    dump("[{$m->semestre}] {$m->nom} → {$m->elementModules->count()} éléments, {$docsCount} docs");
});
