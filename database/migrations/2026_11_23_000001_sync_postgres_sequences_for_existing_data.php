<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $columns = DB::select(<<<'SQL'
            SELECT table_schema, table_name, column_name
            FROM information_schema.columns
            WHERE table_schema = current_schema()
              AND (column_default LIKE 'nextval(%' OR is_identity = 'YES')
            ORDER BY table_name, ordinal_position
        SQL);

        foreach ($columns as $column) {
            $qualifiedTable = '"' . str_replace('"', '""', $column->table_schema)
                . '"."' . str_replace('"', '""', $column->table_name) . '"';
            $quotedColumn = '"' . str_replace('"', '""', $column->column_name) . '"';

            $sequence = DB::selectOne(
                'SELECT pg_get_serial_sequence(?, ?) AS sequence_name',
                [$qualifiedTable, $column->column_name]
            )?->sequence_name;

            if (!$sequence) {
                continue;
            }

            $state = DB::selectOne(
                "SELECT MAX({$quotedColumn}) AS max_id, COUNT(*) > 0 AS has_rows FROM {$qualifiedTable}"
            );

            DB::selectOne(
                'SELECT setval(?::regclass, ?, ?)',
                [$sequence, $state->max_id ?? 1, (bool) $state->has_rows]
            );
        }
    }

    public function down(): void
    {
        // Sequence positions cannot be restored safely after application writes.
    }
};
