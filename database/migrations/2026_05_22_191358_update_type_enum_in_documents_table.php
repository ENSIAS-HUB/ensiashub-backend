<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Élargir l'enum typeDocument pour correspondre aux dossiers Azure :
     *   Cours | TD/TP | Anciens examens | Résumé | Projet | Autre
     * Les valeurs 'TD' → 'TD/TP' et 'Examen' → 'Anciens examens' sont remappées.
     */
    public function up(): void
    {
        // 1. Supprimer la contrainte CHECK existante
        $constraints = DB::select("
            SELECT conname FROM pg_constraint
            WHERE conrelid = 'documents'::regclass
              AND contype = 'c'
              AND pg_get_constraintdef(oid) ILIKE '%typeDocument%'
        ");
        foreach ($constraints as $c) {
            DB::statement("ALTER TABLE documents DROP CONSTRAINT IF EXISTS \"{$c->conname}\"");
        }

        // 2. Remettre à jour les valeurs existantes
        DB::statement("UPDATE documents SET \"typeDocument\" = 'TD/TP' WHERE \"typeDocument\" = 'TD'");
        DB::statement("UPDATE documents SET \"typeDocument\" = 'Anciens examens' WHERE \"typeDocument\" = 'Examen'");

        // 3. Ajouter la nouvelle contrainte CHECK
        DB::statement("
            ALTER TABLE documents
            ADD CONSTRAINT documents_typedocument_check
            CHECK (\"typeDocument\" IN ('Cours','TD/TP','Anciens examens','Résumé','Projet','Autre'))
        ");
    }

    public function down(): void
    {
        $constraints = DB::select("
            SELECT conname FROM pg_constraint
            WHERE conrelid = 'documents'::regclass
              AND contype = 'c'
              AND pg_get_constraintdef(oid) ILIKE '%typeDocument%'
        ");
        foreach ($constraints as $c) {
            DB::statement("ALTER TABLE documents DROP CONSTRAINT IF EXISTS \"{$c->conname}\"");
        }

        DB::statement("UPDATE documents SET \"typeDocument\" = 'TD' WHERE \"typeDocument\" = 'TD/TP'");
        DB::statement("UPDATE documents SET \"typeDocument\" = 'Examen' WHERE \"typeDocument\" = 'Anciens examens'");

        DB::statement("
            ALTER TABLE documents
            ADD CONSTRAINT documents_typedocument_check
            CHECK (\"typeDocument\" IN ('Cours','TD','Examen','Autre'))
        ");
    }
};
