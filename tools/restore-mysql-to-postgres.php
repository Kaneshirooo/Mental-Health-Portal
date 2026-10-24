<?php

/**
 * Restore data from a local MySQL/MariaDB database into a PostgreSQL database.
 *
 * Why this exists
 * ---------------
 * The Render free PostgreSQL instance that backed this app expired, so the
 * production database is gone. The web service itself is fine. Laravel's
 * migrations rebuild the *schema* automatically on the next deploy
 * (see docker/run.sh -> `php artisan migrate --force`), so the only thing that
 * has to be recovered by hand is the *data*.
 *
 * This script copies rows straight from the local MySQL database into the new
 * PostgreSQL database. It reads the target schema from PostgreSQL (not from the
 * dump) and copies only the columns that exist on both sides, so it works even
 * though the migrations are newer than the MySQL snapshot.
 *
 * Usage
 * -----
 *   php -d extension=pdo_pgsql tools/restore-mysql-to-postgres.php ^
 *       --pg-url="postgresql://USER:PASSWORD@HOST/DBNAME?sslmode=require"
 *
 *   # look before you leap
 *   php -d extension=pdo_pgsql tools/restore-mysql-to-postgres.php --pg-url=... --dry-run
 *
 *   # override the local source if needed
 *   php -d extension=pdo_pgsql tools/restore-mysql-to-postgres.php ^
 *       --mysql-db=mental_health_db --mysql-user=root --mysql-pass=
 *
 * Run with --help for the full list of options.
 */

declare(strict_types=1);

// ---------------------------------------------------------------------------
// Options
// ---------------------------------------------------------------------------

/**
 * Tables that are transient / managed by Laravel and must NOT be copied over.
 * Copying `migrations` in particular would make Laravel re-run migrations that
 * already ran on the new database.
 */
const DEFAULT_SKIP = [
    'migrations',
    'cache',
    'cache_locks',
    'sessions',
    'jobs',
    'job_batches',
    'failed_jobs',
    'login_attempts',
    'otp_tokens',
];

function usage(): void
{
    echo <<<TXT
Restore a MySQL/MariaDB database into PostgreSQL.

Options:
  --pg-url=URL          PostgreSQL connection URL (or set PG_TARGET_URL).
                        e.g. postgresql://user:pass@host/dbname?sslmode=require
  --pg-host=HOST        Alternatively supply individual connection parts.
  --pg-port=PORT
  --pg-db=NAME
  --pg-user=USER
  --pg-pass=PASS
  --pg-sslmode=MODE     Default: require
  --mysql-host=HOST     Source MySQL host.       Default: 127.0.0.1
  --mysql-port=PORT     Source MySQL port.       Default: 3306
  --mysql-db=NAME       Source database.         Default: mental_health_db
  --mysql-user=USER     Source user.             Default: root
  --mysql-pass=PASS     Source password.         Default: (empty)
  --chunk=N             Rows per INSERT batch.   Default: 200
  --no-truncate         Do not clear the target tables first.
  --include-all         Copy the transient tables too (migrations, cache, ...).
  --only=a,b,c          Copy only these tables.
  --skip=x,y,z          Extra tables to skip.
  --dry-run             Report what would happen, change nothing.
  --help                Show this message.

TXT;
}

$argvRaw = $argv;
array_shift($argvRaw);

$opt = [
    'pg-url' => getenv('PG_TARGET_URL') ?: null,
    'pg-host' => null,
    'pg-port' => null,
    'pg-db' => null,
    'pg-user' => null,
    'pg-pass' => null,
    'pg-sslmode' => 'require',
    'mysql-host' => '127.0.0.1',
    'mysql-port' => '3306',
    'mysql-db' => 'mental_health_db',
    'mysql-user' => 'root',
    'mysql-pass' => '',
    'chunk' => '200',
    'only' => null,
    'skip' => null,
];

$flags = ['no-truncate' => false, 'include-all' => false, 'dry-run' => false, 'help' => false];

foreach ($argvRaw as $arg) {
    if ($arg === '--help' || $arg === '-h') {
        usage();
        exit(0);
    }

    if (str_starts_with($arg, '--')) {
        $arg = substr($arg, 2);
        $value = null;

        if (str_contains($arg, '=')) {
            [$arg, $value] = explode('=', $arg, 2);
        }

        if (array_key_exists($arg, $flags)) {
            $flags[$arg] = true;
            continue;
        }

        if (! array_key_exists($arg, $opt)) {
            fwrite(STDERR, "Unknown option: --{$arg}\n\n");
            usage();
            exit(2);
        }

        $opt[$arg] = $value;
        continue;
    }

    fwrite(STDERR, "Unexpected argument: {$arg}\n\n");
    usage();
    exit(2);
}

$chunkSize = max(1, (int) $opt['chunk']);
$dryRun = $flags['dry-run'];

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

function fail(string $message): never
{
    fwrite(STDERR, "\n  ERROR  {$message}\n\n");
    exit(1);
}

function info(string $message): void
{
    fwrite(STDOUT, $message . "\n");
}

/** Quote a PostgreSQL identifier (table/column name). */
function pgIdent(string $name): string
{
    return '"' . str_replace('"', '""', $name) . '"';
}

/**
 * Convert a raw MySQL value into something PostgreSQL will accept as text.
 * Every value is bound as a string; PostgreSQL casts it to the target type.
 * We only have to fix up the cases where MySQL's textual form is not valid
 * PostgreSQL input.
 */
function convertValue(mixed $value, string $pgType): ?string
{
    if ($value === null) {
        return null;
    }

    $type = strtolower($pgType);

    if ($type === 'boolean') {
        $normalised = strtolower(trim((string) $value));

        return in_array($normalised, ['1', 'true', 't', 'yes', 'y', 'on'], true) ? 'true' : 'false';
    }

    if (in_array($type, ['smallint', 'integer', 'bigint'], true)) {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : (string) (int) $trimmed;
    }

    if (in_array($type, ['numeric', 'decimal', 'real', 'double precision'], true)) {
        $trimmed = trim((string) $value);

        return $trimmed === '' ? null : (string) (float) $trimmed;
    }

    if (in_array($type, [
        'date',
        'time without time zone',
        'time with time zone',
        'timestamp without time zone',
        'timestamp with time zone',
    ], true)) {
        $trimmed = trim((string) $value);

        // MySQL zero-dates are not representable in PostgreSQL.
        if ($trimmed === '' || str_starts_with($trimmed, '0000-00-00')) {
            return null;
        }

        return $trimmed;
    }

    return (string) $value;
}

/**
 * Fallback value for a PostgreSQL column that is NOT NULL, has no default, and
 * does not exist in the MySQL source at all.
 */
function fallbackFor(string $pgType): string
{
    return match (strtolower($pgType)) {
        'boolean' => 'false',
        'smallint', 'integer', 'bigint', 'numeric', 'decimal', 'real', 'double precision' => '0',
        'date' => '1970-01-01',
        'timestamp without time zone', 'timestamp with time zone' => '1970-01-01 00:00:00',
        default => '',
    };
}

// ---------------------------------------------------------------------------
// Connect
// ---------------------------------------------------------------------------

info('');
info('  MySQL -> PostgreSQL data restore');
info('  ===============================');

if (! extension_loaded('pdo_pgsql')) {
    fail('The pdo_pgsql extension is not loaded. Re-run with: php -d extension=pdo_pgsql ' . basename(__FILE__));
}

try {
    $mysql = new PDO(
        sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4', $opt['mysql-host'], $opt['mysql-port'], $opt['mysql-db']),
        $opt['mysql-user'],
        $opt['mysql-pass'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    fail('Could not connect to MySQL source: ' . $e->getMessage());
}

info(sprintf('  source : mysql://%s@%s:%s/%s', $opt['mysql-user'], $opt['mysql-host'], $opt['mysql-port'], $opt['mysql-db']));

// Build the PostgreSQL DSN.
if ($opt['pg-url']) {
    $parts = parse_url($opt['pg-url']);

    if ($parts === false || ! isset($parts['host'])) {
        fail('Could not parse --pg-url. Expected something like postgresql://user:pass@host/dbname');
    }

    $pgHost = $parts['host'];
    $pgPort = (string) ($parts['port'] ?? 5432);
    $pgDb = ltrim((string) ($parts['path'] ?? ''), '/');
    $pgUser = isset($parts['user']) ? rawurldecode($parts['user']) : '';
    $pgPass = isset($parts['pass']) ? rawurldecode($parts['pass']) : '';

    if ($pgDb === '') {
        fail('--pg-url does not contain a database name.');
    }

    if (isset($parts['query'])) {
        parse_str($parts['query'], $query);
        if (! empty($query['sslmode'])) {
            $opt['pg-sslmode'] = (string) $query['sslmode'];
        }
    }
} else {
    $pgHost = $opt['pg-host'];
    $pgPort = (string) ($opt['pg-port'] ?? 5432);
    $pgDb = $opt['pg-db'];
    $pgUser = $opt['pg-user'];
    $pgPass = (string) ($opt['pg-pass'] ?? '');

    if (! $pgHost || ! $pgDb || ! $pgUser) {
        fail('Supply --pg-url, or all of --pg-host, --pg-db and --pg-user.');
    }
}

$pgDsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $pgHost, $pgPort, $pgDb);

if ($opt['pg-sslmode']) {
    $pgDsn .= ';sslmode=' . $opt['pg-sslmode'];
}

try {
    $pgsql = new PDO($pgDsn, $pgUser, $pgPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pgsql->exec("SET client_encoding TO 'UTF8'");
} catch (PDOException $e) {
    fail('Could not connect to the PostgreSQL target: ' . $e->getMessage());
}

info(sprintf('  target : pgsql://%s@%s:%s/%s (sslmode=%s)', $pgUser, $pgHost, $pgPort, $pgDb, $opt['pg-sslmode'] ?: 'disable'));

// ---------------------------------------------------------------------------
// Read both schemas
// ---------------------------------------------------------------------------

$mysqlTables = [];
foreach ($mysql->query('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"')->fetchAll(PDO::FETCH_NUM) as $row) {
    $mysqlTables[$row[0]] = true;
}

$pgColumns = [];   // table => [column => data_type]
$pgNotNullNoDefault = [];  // table => [column => data_type]
$pgOrder = [];     // table => ordinal position

$stmt = $pgsql->query(<<<'SQL'
    SELECT c.table_name, c.column_name, c.data_type, c.is_nullable, c.column_default
    FROM information_schema.columns c
    JOIN information_schema.tables t
      ON t.table_schema = c.table_schema AND t.table_name = c.table_name
    WHERE c.table_schema = 'public' AND t.table_type = 'BASE TABLE'
    ORDER BY c.table_name, c.ordinal_position
SQL);

foreach ($stmt->fetchAll() as $col) {
    $table = $col['table_name'];
    $pgColumns[$table][$col['column_name']] = $col['data_type'];
    $pgOrder[$table][] = $col['column_name'];

    if ($col['is_nullable'] === 'NO' && $col['column_default'] === null) {
        $pgNotNullNoDefault[$table][$col['column_name']] = $col['data_type'];
    }
}

if ($pgColumns === []) {
    fail(
        'The target PostgreSQL database has no tables. Deploy the app first so that '
        . '`php artisan migrate --force` creates the schema, then run this script again.'
    );
}

// Foreign-key dependency graph, used to insert parents before children.
$deps = [];  // table => [parentTable, ...]
$stmt = $pgsql->query(<<<'SQL'
    SELECT tc.table_name AS child, ccu.table_name AS parent
    FROM information_schema.table_constraints tc
    JOIN information_schema.constraint_column_usage ccu
      ON ccu.constraint_name = tc.constraint_name AND ccu.constraint_schema = tc.constraint_schema
    WHERE tc.constraint_type = 'FOREIGN KEY' AND tc.table_schema = 'public'
SQL);

foreach ($stmt->fetchAll() as $fk) {
    if ($fk['child'] !== $fk['parent']) {
        $deps[$fk['child']][$fk['parent']] = true;
    }
}

// ---------------------------------------------------------------------------
// Decide which tables to copy
// ---------------------------------------------------------------------------

$only = $opt['only'] ? array_filter(array_map('trim', explode(',', (string) $opt['only']))) : null;
$skip = $flags['include-all'] ? [] : DEFAULT_SKIP;

if ($opt['skip']) {
    $skip = array_merge($skip, array_filter(array_map('trim', explode(',', (string) $opt['skip']))));
}

$skip = array_flip($skip);

$plan = [];       // table => [column, ...]
$warnings = [];

foreach ($pgOrder as $table => $columns) {
    if (isset($skip[$table])) {
        continue;
    }

    if ($only !== null && ! in_array($table, $only, true)) {
        continue;
    }

    if (! isset($mysqlTables[$table])) {
        $warnings[] = sprintf('  ! %-28s exists in PostgreSQL but not in MySQL - skipped', $table);
        continue;
    }

    $sourceColumns = [];
    foreach ($mysql->query('SHOW COLUMNS FROM `' . str_replace('`', '``', $table) . '`')->fetchAll() as $col) {
        $sourceColumns[$col['Field']] = true;
    }

    $shared = array_values(array_filter($columns, fn ($c) => isset($sourceColumns[$c])));

    if ($shared === []) {
        $warnings[] = sprintf('  ! %-28s no shared columns - skipped', $table);
        continue;
    }

    // PostgreSQL-only NOT NULL columns without a default must be filled in.
    foreach ($pgNotNullNoDefault[$table] ?? [] as $column => $type) {
        if (! isset($sourceColumns[$column])) {
            $warnings[] = sprintf(
                '  ! %-28s column "%s" (%s) is NOT NULL with no default and missing from MySQL - filling with %s',
                $table,
                $column,
                $type,
                fallbackFor($type)
            );
        }
    }

    $plan[$table] = $shared;
}

// MySQL tables that PostgreSQL does not know about cannot be restored.
foreach (array_keys($mysqlTables) as $table) {
    if (! isset($pgColumns[$table])) {
        $warnings[] = sprintf('  ! %-28s exists in MySQL but not in PostgreSQL - skipped', $table);
    }
}

if ($plan === []) {
    fail('Nothing to copy - no table exists on both sides.');
}

// Topologically sort so that referenced rows exist before referencing rows.
$sorted = [];
$visiting = [];

$visit = function (string $table) use (&$visit, &$sorted, &$visiting, $deps, $plan): void {
    if (isset($sorted[$table]) || isset($visiting[$table])) {
        return;
    }

    $visiting[$table] = true;

    foreach (array_keys($deps[$table] ?? []) as $parent) {
        if (isset($plan[$parent])) {
            $visit($parent);
        }
    }

    unset($visiting[$table]);
    $sorted[$table] = true;
};

foreach (array_keys($plan) as $table) {
    $visit($table);
}

$ordered = array_keys($sorted);

// ---------------------------------------------------------------------------
// Report the plan
// ---------------------------------------------------------------------------

info('');
info(sprintf('  tables to copy: %d', count($ordered)));
info('');

if ($warnings !== []) {
    info('  Warnings');
    foreach ($warnings as $warning) {
        info($warning);
    }
    info('');
}

$totalRows = 0;
$counts = [];

foreach ($ordered as $table) {
    $count = (int) $mysql->query('SELECT COUNT(*) FROM `' . str_replace('`', '``', $table) . '`')->fetchColumn();
    $counts[$table] = $count;
    $totalRows += $count;
    info(sprintf('  %-28s %6d rows  (%d columns)', $table, $count, count($plan[$table])));
}

info('');
info(sprintf('  total: %d rows', $totalRows));
info('');

if ($dryRun) {
    info('  --dry-run: nothing was written.');
    info('');
    exit(0);
}

// ---------------------------------------------------------------------------
// Copy
// ---------------------------------------------------------------------------

if (! $flags['no-truncate']) {
    info('  Clearing target tables ...');
}

$pgsql->beginTransaction();

try {
    if (! $flags['no-truncate']) {
        // One TRUNCATE for every table keeps foreign keys happy.
        $pgsql->exec('TRUNCATE TABLE ' . implode(', ', array_map('pgIdent', $ordered)) . ' RESTART IDENTITY CASCADE');
    }

    $inserted = [];

    foreach ($ordered as $table) {
        $columns = $plan[$table];

        // Append any NOT NULL / no-default columns that the source lacks.
        foreach ($pgNotNullNoDefault[$table] ?? [] as $column => $type) {
            if (! in_array($column, $columns, true)) {
                $columns[] = $column;
            }
        }

        $columnList = implode(', ', array_map('pgIdent', $columns));
        $rowPlaceholders = '(' . implode(', ', array_fill(0, count($columns), '?')) . ')';

        $select = 'SELECT ' . implode(', ', array_map(fn ($c) => '`' . str_replace('`', '``', $c) . '`', $columns))
            . ' FROM `' . str_replace('`', '``', $table) . '`';

        $read = $mysql->query($select);
        $written = 0;
        $batch = [];
        $batchPlaceholders = [];

        $flush = function () use (&$batch, &$batchPlaceholders, &$written, $pgsql, $table, $columnList, $rowPlaceholders): void {
            if ($batch === []) {
                return;
            }

            $sql = sprintf(
                'INSERT INTO %s (%s) VALUES %s',
                pgIdent($table),
                $columnList,
                implode(', ', $batchPlaceholders)
            );

            $stmt = $pgsql->prepare($sql);

            foreach ($batch as $index => $row) {
                foreach ($row as $position => $value) {
                    $stmt->bindValue($index * count($row) + $position + 1, $value, $value === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
                }
            }

            $stmt->execute();
            $written += count($batch);
            $batch = [];
            $batchPlaceholders = [];
        };

        while ($row = $read->fetch(PDO::FETCH_ASSOC)) {
            $converted = [];

            foreach ($columns as $column) {
                $converted[] = convertValue($row[$column] ?? null, $pgColumns[$table][$column]);
            }

            $batch[] = $converted;
            $batchPlaceholders[] = $rowPlaceholders;

            if (count($batch) >= $chunkSize) {
                $flush();
            }
        }

        $flush();

        $inserted[$table] = $written;
        info(sprintf('  %-28s %6d / %d rows', $table, $written, $counts[$table]));
    }

    // Realign every sequence with the highest id we just inserted, otherwise the
    // next INSERT collides with an existing primary key.
    info('');
    info('  Realigning sequences ...');

    foreach ($ordered as $table) {
        foreach ($pgColumns[$table] as $column => $type) {
            if (! in_array($type, ['smallint', 'integer', 'bigint'], true)) {
                continue;
            }

            $sequence = $pgsql->query(
                'SELECT pg_get_serial_sequence(' . $pgsql->quote($table) . ', ' . $pgsql->quote($column) . ')'
            )->fetchColumn();

            if (! $sequence) {
                continue;
            }

            $sql = sprintf(
                'SELECT setval(%s, COALESCE((SELECT MAX(%s) FROM %s), 1), (SELECT COUNT(*) FROM %s) > 0)',
                $pgsql->quote((string) $sequence),
                pgIdent($column),
                pgIdent($table),
                pgIdent($table)
            );

            $pgsql->exec($sql);
            info(sprintf('  %-28s %s -> %s', $table . '.' . $column, $sequence, 'ok'));
        }
    }

    $pgsql->commit();
} catch (Throwable $e) {
    $pgsql->rollBack();
    fail('Copy failed, nothing was written: ' . $e->getMessage());
}

info('');
info('  Done. All rows copied in a single transaction.');
info('');

$mismatch = [];
foreach ($inserted as $table => $written) {
    if ($written !== $counts[$table]) {
        $mismatch[] = sprintf('%s (%d of %d)', $table, $written, $counts[$table]);
    }
}

if ($mismatch !== []) {
    info('  WARNING - row counts did not match for: ' . implode(', ', $mismatch));
    info('');
    exit(1);
}

info('  Every table copied completely.');
info('');
