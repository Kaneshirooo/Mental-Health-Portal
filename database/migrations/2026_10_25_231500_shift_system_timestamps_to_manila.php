<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Times the user chose for themselves. Shifting these would invent a
     * time nobody picked, so they are deliberately left exactly as saved.
     * (Plain DATE columns - birthdays, follow-up dates - are excluded
     * automatically because they carry no time to convert.)
     */
    private const USER_PICKED = [
        'appointments.scheduled_at',
    ];

    /**
     * The app ran on UTC until this migration, so every system-generated
     * stamp was written 8 hours behind the Philippines. Move them forward
     * so history matches the wall clock the user was actually looking at.
     */
    private const OFFSET_HOURS = 8;

    public function up(): void
    {
        $this->shift(self::OFFSET_HOURS);
    }

    public function down(): void
    {
        $this->shift(-self::OFFSET_HOURS);
    }

    private function shift(int $hours): void
    {
        foreach ($this->timestampColumns() as $table => $columns) {
            $columns = array_values(array_filter(
                $columns,
                fn (string $column) => ! in_array($table . '.' . $column, self::USER_PICKED, true)
            ));

            if ($columns === []) {
                continue;
            }

            DB::statement($this->updateSql($table, $columns, $hours));
        }
    }

    /**
     * Every timestamp column in the schema, grouped by table.
     */
    private function timestampColumns(): array
    {
        $rows = DB::getDriverName() === 'pgsql'
            ? DB::select("
                SELECT table_name, column_name
                FROM information_schema.columns
                WHERE table_schema = 'public'
                  AND data_type LIKE 'timestamp%'
                ORDER BY table_name, ordinal_position
            ")
            : DB::select("
                SELECT TABLE_NAME AS table_name, COLUMN_NAME AS column_name
                FROM information_schema.columns
                WHERE TABLE_SCHEMA = DATABASE()
                  AND DATA_TYPE IN ('datetime', 'timestamp')
                ORDER BY TABLE_NAME, ORDINAL_POSITION
            ");

        $grouped = [];
        foreach ($rows as $row) {
            $grouped[$row->table_name][] = $row->column_name;
        }

        return $grouped;
    }

    /**
     * Every column is assigned in a single statement. On MySQL that matters:
     * a per-column UPDATE would leave the other timestamp columns
     * unassigned, and their ON UPDATE CURRENT_TIMESTAMP would then silently
     * overwrite the very values we just shifted.
     */
    private function updateSql(string $table, array $columns, int $hours): string
    {
        $pgsql    = DB::getDriverName() === 'pgsql';
        $q        = $pgsql ? '"' : '`';
        $interval = $pgsql
            ? sprintf("INTERVAL '%d hours'", $hours)
            : sprintf('INTERVAL %d HOUR', $hours);

        $set = implode(', ', array_map(
            fn (string $column) => sprintf('%1$s%2$s%1$s = %1$s%2$s%1$s + %3$s', $q, $column, $interval),
            $columns
        ));

        // NULL + an interval is still NULL, so rows that never got a stamp
        // are left alone without needing a WHERE clause.
        return sprintf('UPDATE %1$s%2$s%1$s SET %3$s', $q, $table, $set);
    }
};
