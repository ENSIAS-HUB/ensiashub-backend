<?php
dump('Queue driver : ' . config('queue.default'));
dump('Jobs table   : ' . (Schema::hasTable('jobs') ? 'OK' : 'MANQUANTE'));
dump('Failed jobs  : ' . (Schema::hasTable('failed_jobs') ? 'OK' : 'MANQUANTE'));

// Colonnes de la table documents
$cols = DB::select("SELECT column_name FROM information_schema.columns WHERE table_name='documents' ORDER BY ordinal_position");
$colNames = array_map(fn($c) => $c->column_name, $cols);
dump('Documents colonnes : ' . implode(', ', $colNames));
