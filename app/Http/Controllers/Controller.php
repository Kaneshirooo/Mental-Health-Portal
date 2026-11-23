<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

abstract class Controller
{
    /**
     * Start a transaction that is guaranteed to be usable.
     *
     * When a connection is handed back already aborted (SQLSTATE 25P02), the
     * first INSERT fails with "current transaction is aborted" and the real
     * cause of the failure is never reported. Clearing any leftover
     * server-side transaction state first means every attempt starts from a
     * clean slate, and the probe below proves it before the first real write.
     */
    protected function beginCleanTransaction(): void
    {
        // 1. Bring Laravel's own transaction bookkeeping back to zero.
        if (DB::transactionLevel() > 0) {
            try {
                DB::rollBack();
            } catch (\Throwable $e) {
                // Nothing left to roll back.
            }
        }

        // 2. Clear an aborted/leftover transaction the server still holds on
        //    this connection. ROLLBACK outside a transaction is only a warning
        //    in PostgreSQL, so this is always safe.
        try {
            DB::unprepared('ROLLBACK');
        } catch (\Throwable $e) {
            // Nothing to clear.
        }

        // 3. Start a real transaction.
        DB::beginTransaction();

        // 4. Prove it is usable before the first real write, and start over
        //    once if the connection was dead on arrival.
        try {
            DB::select('select 1');
        } catch (\Throwable $e) {
            try {
                DB::rollBack();
            } catch (\Throwable $ignore) {
                // Already clean.
            }
            try {
                DB::unprepared('ROLLBACK');
            } catch (\Throwable $ignore) {
                // Already clean.
            }

            DB::beginTransaction();
        }
    }

    /**
     * True when the failure is the "transaction already aborted" cascade
     * (SQLSTATE 25P02) rather than a real problem with the data being saved.
     *
     * A cascade is worth retrying once on a clean transaction; anything else
     * (duplicate key, missing column, ...) must be reported straight away.
     */
    protected function isAbortedTransactionCascade(\Throwable $e): bool
    {
        $message = $e->getMessage();

        return str_contains($message, '25P02')
            || str_contains($message, 'current transaction is aborted')
            || str_contains($message, 'In failed sql transaction');
    }

    protected function debugLog(string $hypothesisId, string $location, string $message, array $data = []): void
    {
        // #region agent log
        file_put_contents(
            storage_path('logs/debug-a97deb.log'),
            json_encode([
                'sessionId' => 'a97deb',
                'runId' => 'initial',
                'hypothesisId' => $hypothesisId,
                'location' => $location,
                'message' => $message,
                'data' => $data,
                'timestamp' => (int) round(microtime(true) * 1000),
            ], JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND
        );
        // #endregion
    }
}
