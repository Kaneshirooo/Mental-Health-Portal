<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The `users` table was created with several columns shorter than what the
 * application actually accepts (e.g. contact_number varchar(15) while the
 * form allows 20, course varchar(100) while the form allows 255, and in the
 * live database even full_name / roll_number can be varchar(50)).
 *
 * A longer-than-expected value then reaches the database and blows up with
 * "value too long for type character varying(n)" — a raw SQL error shown to
 * the end user instead of a friendly message.
 *
 * Widening those columns keeps the database aligned with the validated input
 * limits so a registration can never crash on a long value.
 */
return new class extends Migration
{
    /**
     * column => [length, nullable]
     */
    protected function targetColumns(): array
    {
        return [
            'full_name'           => [255, false],
            'email'               => [255, false],
            'roll_number'         => [255, true],
            'contact_number'      => [50, true],
            'department'          => [255, true],
            'course'              => [255, true],
            'semester'            => [100, true],
            'year_section'        => [50, true],
            'guardian_name'       => [255, true],
            'guardian_relationship' => [100, true],
            'guardian_contact'    => [30, true],
            'guardian_email'      => [255, true],
            'id_proof_path'       => [255, true],
            'profile_picture'     => [255, true],
        ];
    }

    public function up(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $driver = DB::getDriverName();

        foreach ($this->targetColumns() as $column => [$length, $nullable]) {
            if (!Schema::hasColumn('users', $column)) {
                continue;
            }

            if ($driver === 'pgsql') {
                // Only the type is changed — nullability, defaults and indexes stay intact.
                DB::statement('ALTER TABLE "users" ALTER COLUMN "' . $column . '" TYPE varchar(' . $length . ')');
            } else {
                Schema::table('users', function (Blueprint $table) use ($column, $length, $nullable) {
                    $definition = $table->string($column, $length);
                    if ($nullable) {
                        $definition->nullable();
                    }
                    $definition->change();
                });
            }
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('users')) {
            return;
        }

        $driver = DB::getDriverName();

        $original = [
            'full_name'           => [255, false],
            'email'               => [255, false],
            'roll_number'         => [50, true],
            'contact_number'      => [15, true],
            'department'          => [100, true],
            'course'              => [100, true],
            'semester'            => [20, true],
            'year_section'        => [50, true],
            'guardian_name'       => [255, true],
            'guardian_relationship' => [100, true],
            'guardian_contact'    => [30, true],
            'guardian_email'      => [255, true],
            'id_proof_path'       => [255, true],
            'profile_picture'     => [255, true],
        ];

        foreach ($original as $column => [$length, $nullable]) {
            if (!Schema::hasColumn('users', $column)) {
                continue;
            }

            if ($driver === 'pgsql') {
                DB::statement('ALTER TABLE "users" ALTER COLUMN "' . $column . '" TYPE varchar(' . $length . ')');
            } else {
                Schema::table('users', function (Blueprint $table) use ($column, $length, $nullable) {
                    $definition = $table->string($column, $length);
                    if ($nullable) {
                        $definition->nullable();
                    }
                    $definition->change();
                });
            }
        }
    }
};
