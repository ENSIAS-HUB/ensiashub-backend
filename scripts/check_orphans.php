<?php
$docs = DB::table('documents')->whereNull('element_module_id')->get(['id','titre','azure_path','module_pedagogique_id']);
foreach ($docs as $d) {
    dump([
        'id'                    => $d->id,
        'titre'                 => substr($d->titre ?? '', 0, 60),
        'azure_path'            => $d->azure_path,
        'module_pedagogique_id' => $d->module_pedagogique_id,
    ]);
}
echo "Total orphelins: " . count($docs) . PHP_EOL;
