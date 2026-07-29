<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;

class TableImportExportService
{
    /**
     * Uses the app's default database connection. Confirm your .env's
     * DB_DATABASE is set to psa_website — that's what actually scopes
     * this to the right database, not a Laravel "connection" name.
     */
    protected ?string $connection = null;

    protected function db()
    {
        return DB::connection($this->connection);
    }

    /**
     * Insert a row, treating a duplicate-key violation (1062) as "already
     * exists — skip" instead of letting it crash the whole import. This is
     * a safety net on top of the upfront guessUniqueKey() dedupe check.
     */
    protected function insertOrSkip(string $table, array $data): bool
    {
        try {
            $this->db()->table($table)->insert($data);
            return true;
        } catch (\Illuminate\Database\QueryException $e) {
            if ((int) ($e->errorInfo[1] ?? 0) === 1062) {
                return false;
            }
            throw $e;
        }
    }

    protected function schema()
    {
        return Schema::connection($this->connection);
    }

    /**
     * Read just the header row of a CSV (or the column list of a CREATE TABLE
     * statement if a .sql file is given) without loading the whole file.
     */
    public function peekHeaders(string $path): array
    {
        if (Str::endsWith($path, '.sql')) {
            $sql = file_get_contents($path);
            if (preg_match('/CREATE TABLE[^(]*\(([^;]*)\)\s*(ENGINE|;)/is', $sql, $m)) {
                preg_match_all('/^\s*`?(\w+)`?\s+[A-Z]/mi', $m[1], $cols);
                return $cols[1] ?? [];
            }
            return [];
        }

        $handle = fopen($path, 'r');
        $headers = fgetcsv($handle) ?: [];
        fclose($handle);

        return array_map('trim', $headers);
    }

    /**
     * Import a single table from CSV or SQL, into the psa_website database only.
     *
     * Rule: if the table exists, its header/columns must match the file's
     * header/columns. Existing rows (matched on primary key / unique columns)
     * are skipped; only new rows are inserted. If the table doesn't exist and
     * $createIfMissing is true, it's created from the CSV header or the SQL
     * file's CREATE TABLE statement.
     */
    public function importTable(string $path, string $table, bool $createIfMissing = false): array
    {
        $isSql = Str::endsWith($path, '.sql');
        $exists = $this->schema()->hasTable($table);

        if (! $exists && ! $createIfMissing) {
            throw new \RuntimeException("Table `{$table}` does not exist in `{$this->connection}`.");
        }

        if ($isSql) {
            return $exists
                ? $this->importSqlIntoExistingTable($path, $table)
                : $this->importSqlCreatingTable($path, $table);
        }

        return $exists
            ? $this->importCsvIntoExistingTable($path, $table)
            : $this->importCsvCreatingTable($path, $table);
    }

    protected function importCsvCreatingTable(string $path, string $table): array
    {
        $handle = fopen($path, 'r');
        $headers = array_map('trim', fgetcsv($handle));

        $this->schema()->create($table, function ($t) use ($headers) {
            $t->id();
            foreach ($headers as $col) {
                // id is handled by $t->id() above; created_at/updated_at are
                // added by $t->timestamps() below — skip both here so we
                // don't try to add the same column twice (MySQL 1060 error).
                if (in_array($col, ['id', 'created_at', 'updated_at'])) {
                    continue;
                }
                $t->string($col)->nullable();
            }
            $t->timestamps();
        });

        $inserted = 0;
        $skipped = 0;
        $headerCount = count($headers);

        while ($row = fgetcsv($handle)) {
            // Skip blank/trailing lines (fgetcsv returns [null] for a blank line).
            if ($row === [null] || $row === false) {
                continue;
            }

            if (count($row) !== $headerCount) {
                // Row doesn't match header column count — skip it rather than crash.
                continue;
            }

            $data = array_combine($headers, $row);
            unset($data['id']);

            if ($this->insertOrSkip($table, $data)) {
                $inserted++;
            } else {
                $skipped++;
            }
        }
        fclose($handle);

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    protected function importCsvIntoExistingTable(string $path, string $table): array
    {
        $handle = fopen($path, 'r');
        $headers = array_map('trim', fgetcsv($handle));

        $existingColumns = $this->schema()->getColumnListing($table);
        $missing = array_diff($headers, $existingColumns);

        if (! empty($missing)) {
            fclose($handle);
            throw new \RuntimeException(
                'CSV headers do not match table `' . $table . '` columns in `' . $this->connection . '`. Unexpected column(s): ' . implode(', ', $missing)
            );
        }

        $uniqueKey = $this->guessUniqueKey($table, $headers);

        $inserted = 0;
        $skipped = 0;
        $headerCount = count($headers);

        while ($row = fgetcsv($handle)) {
            // Skip blank/trailing lines (fgetcsv returns [null] for a blank line).
            if ($row === [null] || $row === false) {
                continue;
            }

            if (count($row) !== $headerCount) {
                // Row doesn't match header column count — skip rather than crash.
                $skipped++;
                continue;
            }

            $data = array_combine($headers, $row);

            if ($uniqueKey && isset($data[$uniqueKey])) {
                $existsRow = $this->db()->table($table)->where($uniqueKey, $data[$uniqueKey])->exists();
                if ($existsRow) {
                    $skipped++;
                    continue;
                }
            }

            if ($this->insertOrSkip($table, $data)) {
                $inserted++;
            } else {
                $skipped++;
            }
        }
        fclose($handle);

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    protected function importSqlCreatingTable(string $path, string $table): array
    {
        // Run the CREATE TABLE + INSERT statements as-is (assumes the dump
        // was scoped to this one table), against the psa_website connection.
        $sql = file_get_contents($path);
        $statements = array_filter(array_map('trim', explode(';', $sql)));

        $inserted = 0;
        foreach ($statements as $statement) {
            if ($statement === '') {
                continue;
            }
            $this->db()->statement($statement);
            if (stripos($statement, 'insert into') === 0) {
                $inserted++;
            }
        }

        return ['inserted' => $inserted, 'skipped' => 0];
    }

    protected function importSqlIntoExistingTable(string $path, string $table): array
    {
        $sql = file_get_contents($path);

        // Pull column list out of the dump's INSERT statements to verify
        // they match the existing table before touching anything.
        if (preg_match('/INSERT INTO\s+`?' . preg_quote($table, '/') . '`?\s*\(([^)]+)\)/i', $sql, $m)) {
            $sqlColumns = array_map(fn ($c) => trim($c, " `\t\n"), explode(',', $m[1]));
            $existingColumns = $this->schema()->getColumnListing($table);
            $missing = array_diff($sqlColumns, $existingColumns);

            if (! empty($missing)) {
                throw new \RuntimeException(
                    'SQL columns do not match table `' . $table . '` columns in `' . $this->connection . '`. Unexpected column(s): ' . implode(', ', $missing)
                );
            }
        }

        preg_match_all(
            '/INSERT INTO\s+`?' . preg_quote($table, '/') . '`?\s*\(([^)]+)\)\s*VALUES\s*\((.+?)\);/is',
            $sql,
            $matches,
            PREG_SET_ORDER
        );

        $uniqueKey = null;
        $inserted = 0;
        $skipped = 0;

        foreach ($matches as $match) {
            $columns = array_map(fn ($c) => trim($c, " `\t\n"), explode(',', $match[1]));

            // A dump can have multiple value tuples per INSERT: (a,b),(c,d),(e,f)
            $valueTuples = $this->splitValueTuples($match[2]);

            foreach ($valueTuples as $tupleRaw) {
                $values = str_getcsv($tupleRaw, ',', "'");

                if (count($values) !== count($columns)) {
                    // Mismatched column/value count — skip this row rather than crash.
                    $skipped++;
                    continue;
                }

                $data = array_combine($columns, $values);

                $uniqueKey ??= $this->guessUniqueKey($table, $columns);

                if ($uniqueKey && isset($data[$uniqueKey])) {
                    if ($this->db()->table($table)->where($uniqueKey, $data[$uniqueKey])->exists()) {
                        $skipped++;
                        continue;
                    }
                }

                if ($this->insertOrSkip($table, $data)) {
                    $inserted++;
                } else {
                    $skipped++;
                }
            }
        }

        return ['inserted' => $inserted, 'skipped' => $skipped];
    }

    /**
     * Split a VALUES clause body into individual row tuples, respecting
     * quoted strings so commas/parens inside values don't break the split.
     * Input like: "1,'a,b'),(2,'c'" -> ["1,'a,b'", "2,'c'"]
     */
    protected function splitValueTuples(string $body): array
    {
        // Re-wrap so we can uniformly find "(...)" groups even for a single tuple.
        $wrapped = '(' . $body . ')';

        preg_match_all('/\(((?:[^()\']|\'(?:[^\'\\\\]|\\\\.)*\')*)\)/', $wrapped, $tuples);

        return $tuples[1] ?? [$body];
    }

    /**
     * Pick a column to detect "already exists" on: the table's real primary
     * key first (however it's named), otherwise a unique-looking column
     * present in both the file and the table (falls back to null = no
     * dedupe, just insert all — duplicates are still caught defensively
     * at insert time).
     */
    protected function guessUniqueKey(string $table, array $fileColumns): ?string
    {
        $primaryKey = $this->getPrimaryKeyColumn($table);

        if ($primaryKey && in_array($primaryKey, $fileColumns)) {
            return $primaryKey;
        }

        if (in_array('id', $fileColumns) && $this->schema()->hasColumn($table, 'id')) {
            return 'id';
        }

        foreach (['uuid', 'email', 'code', 'slug'] as $candidate) {
            if (in_array($candidate, $fileColumns) && $this->schema()->hasColumn($table, $candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Look up the table's actual primary key column name from information_schema,
     * so dedupe works even when the PK isn't called "id" (e.g. member_id_no).
     */
    protected function getPrimaryKeyColumn(string $table): ?string
    {
        $connectionName = $this->connection ?? config('database.default');
        $databaseName = config("database.connections.{$connectionName}.database");

        $row = $this->db()->selectOne(
            "SELECT COLUMN_NAME
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = ?
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = 'PRIMARY'
             LIMIT 1",
            [$databaseName, $table]
        );

        return $row->COLUMN_NAME ?? null;
    }

    /**
     * Import a full database dump (.sql) into psa_website only. Splits on
     * statement boundaries and runs each one; existing rows are skipped
     * where a matching dedupe key is found.
     */
    public function importDatabase(string $path): array
    {
        $sql = file_get_contents($path);
        $statements = $this->splitSqlStatements($sql);

        $tables = [];
        $inserted = 0;
        $skipped = 0;

        foreach ($statements as $statement) {
            if (preg_match('/CREATE TABLE\s+`?(\w+)`?/i', $statement, $m)) {
                $tables[$m[1]] = true;
                if (! $this->schema()->hasTable($m[1])) {
                    $this->db()->statement($statement);
                }
                continue;
            }

            if (preg_match('/INSERT INTO\s+`?(\w+)`?\s*\(([^)]+)\)\s*VALUES\s*\((.+)\)/is', $statement, $m)) {
                $table = $m[1];
                $columns = array_map(fn ($c) => trim($c, " `\t\n"), explode(',', $m[2]));
                $valueTuples = $this->splitValueTuples($m[3]);

                foreach ($valueTuples as $tupleRaw) {
                    $values = str_getcsv($tupleRaw, ',', "'");

                    if (count($values) !== count($columns)) {
                        $skipped++;
                        continue;
                    }

                    $data = array_combine($columns, $values);

                    $uniqueKey = $this->guessUniqueKey($table, $columns);

                    if ($uniqueKey && isset($data[$uniqueKey]) && $this->db()->table($table)->where($uniqueKey, $data[$uniqueKey])->exists()) {
                        $skipped++;
                        continue;
                    }

                    if ($this->insertOrSkip($table, $data)) {
                        $inserted++;
                    } else {
                        $skipped++;
                    }
                }
                continue;
            }

            // Any other statement (SET, DROP, ALTER, etc.) — run as-is against psa_website.
            if (trim($statement) !== '') {
                $this->db()->statement($statement);
            }
        }

        return ['tables' => count($tables), 'inserted' => $inserted, 'skipped' => $skipped];
    }

    protected function splitSqlStatements(string $sql): array
    {
        return array_filter(array_map('trim', explode(';', $sql)));
    }

    // ---------------------------------------------------------------
    // EXPORT
    // ---------------------------------------------------------------

    public function exportTable(string $table, string $format): string
    {
        $dir = storage_path('app/exports');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        return $format === 'sql'
            ? $this->exportTableAsSql($table, $dir)
            : $this->exportTableAsCsv($table, $dir);
    }

    protected function exportTableAsCsv(string $table, string $dir): string
    {
        $path = "{$dir}/{$table}.csv";
        $handle = fopen($path, 'w');

        $columns = $this->schema()->getColumnListing($table);
        fputcsv($handle, $columns);

        $this->db()->table($table)->orderBy($columns[0])->chunk(500, function ($rows) use ($handle, $columns) {
            foreach ($rows as $row) {
                $row = (array) $row;
                fputcsv($handle, array_map(fn ($c) => $row[$c] ?? '', $columns));
            }
        });

        fclose($handle);

        return $path;
    }

    protected function exportTableAsSql(string $table, string $dir): string
    {
        $path = "{$dir}/{$table}.sql";
        $handle = fopen($path, 'w');

        $columns = $this->schema()->getColumnListing($table);
        $columnList = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));

        $this->db()->table($table)->orderBy($columns[0])->chunk(500, function ($rows) use ($handle, $table, $columns, $columnList) {
            foreach ($rows as $row) {
                $row = (array) $row;
                $values = implode(', ', array_map(function ($c) use ($row) {
                    $v = $row[$c] ?? null;
                    return $v === null ? 'NULL' : "'" . addslashes($v) . "'";
                }, $columns));

                fwrite($handle, "INSERT INTO `{$table}` ({$columnList}) VALUES ({$values});\n");
            }
        });

        fclose($handle);

        return $path;
    }

    /**
     * Full database export, scoped strictly to psa_website. Uses mysqldump
     * when available (MySQL), otherwise falls back to a manual per-table
     * SQL dump built from Schema/DB facades bound to this connection.
     */
    public function exportDatabase(): string
    {
        $dir = storage_path('app/exports');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $config = config("database.connections.{$this->connection}");
        $driver = $config['driver'] ?? null;

        $path = "{$dir}/{$this->connection}-" . now()->format('Ymd_His') . '.sql';

        if ($driver === 'mysql' && $this->commandExists('mysqldump')) {
            $process = new Process([
                'mysqldump',
                '-h', $config['host'],
                '-u', $config['username'],
                '--password=' . $config['password'],
                $config['database'],
            ]);
            $process->setTimeout(300);
            $process->run();

            file_put_contents($path, $process->getOutput());

            return $path;
        }

        // Fallback: dump every user table manually, scoped to this connection.
        $handle = fopen($path, 'w');
        foreach ($this->schema()->getTableListing() as $table) {
            $columns = $this->schema()->getColumnListing($table);
            $columnList = implode(', ', array_map(fn ($c) => "`{$c}`", $columns));

            $this->db()->table($table)->orderBy($columns[0])->chunk(500, function ($rows) use ($handle, $table, $columns, $columnList) {
                foreach ($rows as $row) {
                    $row = (array) $row;
                    $values = implode(', ', array_map(function ($c) use ($row) {
                        $v = $row[$c] ?? null;
                        return $v === null ? 'NULL' : "'" . addslashes($v) . "'";
                    }, $columns));
                    fwrite($handle, "INSERT INTO `{$table}` ({$columnList}) VALUES ({$values});\n");
                }
            });
        }
        fclose($handle);

        return $path;
    }

    protected function commandExists(string $command): bool
    {
        $result = shell_exec(sprintf('which %s 2>/dev/null', escapeshellarg($command)));
        return ! empty($result);
    }
}