<?php

namespace Renderbit\DbUrlReplacer\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReplaceUrlInDatabase extends Command
{
    protected $signature = 'db:replace-url
                            {oldUrl : The URL to replace}
                            {newUrl : The new URL}
                            {--tables= : Comma-separated list of table names}
                            {--columns= : Comma-separated list of column names}
                            {--dry-run : Only show what would be updated}';

    protected $description = 'Search and replace URLs in the database with optional table/column filtering and dry-run support';

    public function handle()
    {
        $oldUrl = $this->argument('oldUrl');
        $newUrl = $this->argument('newUrl');
        $tablesFilter = $this->option('tables') ? explode(',', $this->option('tables')) : [];
        $columnsFilter = $this->option('columns') ? explode(',', $this->option('columns')) : [];
        $isDryRun = $this->option('dry-run');

        if (!filter_var($oldUrl, FILTER_VALIDATE_URL)) {
            $this->error("Invalid old URL: $oldUrl");
            return Command::FAILURE;
        }

        if (!filter_var($newUrl, FILTER_VALIDATE_URL)) {
            $this->error("Invalid new URL: $newUrl");
            return Command::FAILURE;
        }

        $schema = DB::connection()->getSchemaBuilder();
        $useNativeSchema = method_exists($schema, 'getTables');

        if ($useNativeSchema) {
            $allTables = collect($schema->getTables())->pluck('name')->all();
        } else {
            $allTables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        }

        if ($tablesFilter) {
            $invalidTables = array_diff($tablesFilter, $allTables);
            if (!empty($invalidTables)) {
                $this->error('Invalid table(s): ' . implode(', ', $invalidTables));
                return Command::FAILURE;
            }
        }

        $columnsToProcess = [];
        $columnFoundInFilter = false;

        foreach ($allTables as $table) {
            // Skip tables if filter applies and table is not in filter
            if ($tablesFilter && !in_array($table, $tablesFilter)) {
                continue;
            }

            $columns = [];
            if ($useNativeSchema && method_exists($schema, 'getColumns')) {
                // Laravel 11+ way
                $cols = $schema->getColumns($table);
                foreach ($cols as $col) {
                    $typeName = strtolower($col['type_name']);
                    // SQLite returns 'varchar', MySQL 'varchar', Postgres 'character varying'
                    // We need a loose check or rely on mapping
                    if ($this->isTextType($typeName)) {
                        $columns[] = $col['name'];
                    }
                }
            } else {
                // DBAL way
                $cols = DB::connection()->getDoctrineSchemaManager()->listTableColumns($table);
                foreach ($cols as $col) {
                    // DBAL returns Type object
                    $typeName = $col->getType()->getName(); // 'string', 'text', etc.
                     if ($this->isTextType($typeName)) {
                        $columns[] = $col->getName();
                    }
                }
            }

            foreach ($columns as $column) {
                 if ($columnsFilter) {
                    if (in_array($column, $columnsFilter)) {
                        $columnFoundInFilter = true;
                        $columnsToProcess[] = ['table' => $table, 'column' => $column];
                    }
                } else {
                    $columnsToProcess[] = ['table' => $table, 'column' => $column];
                }
            }
        }

        if ($columnsFilter && !$columnFoundInFilter) {
             $this->error('None of the specified columns were found in any table.');
             return Command::FAILURE;
        }

        $results = [];

        foreach ($columnsToProcess as $item) {
            $table = $item['table'];
            $column = $item['column'];

            try {
                $count = DB::table($table)
                    ->where($column, 'LIKE', "%$oldUrl%")
                    ->count();

                if ($count > 0) {
                    $replaced = $isDryRun ? 0 : DB::table($table)
                        ->where($column, 'LIKE', "%$oldUrl%")
                        ->update([
                            $column => DB::raw("REPLACE($column, '" . addslashes($oldUrl) . "', '" . addslashes($newUrl) . "')")
                        ]);

                    $results[] = [
                        'table' => $table,
                        'column' => $column,
                        'matches' => $count,
                        'replaced' => $isDryRun ? 0 : $replaced,
                    ];
                }
            } catch (\Throwable $e) {
                $this->warn("Skipped $table.$column due to error: " . $e->getMessage());
            }
        }

        if (empty($results)) {
            $this->info('No matches found.');
        } else {
            $this->table(
                ['Table', 'Column', 'Matches Found', $isDryRun ? 'Would Replace' : 'Replaced'],
                collect($results)->map(fn ($row) => [
                    $row['table'],
                    $row['column'],
                    $row['matches'],
                    $isDryRun ? $row['matches'] : $row['replaced']
                ])->toArray()
            );

            $this->info($isDryRun ? 'Dry run complete. No changes were made.' : 'Replacement complete.');
        }

        return Command::SUCCESS;
    }

    protected function isTextType($type)
    {
        $textTypes = ['varchar', 'text', 'longtext', 'mediumtext', 'char', 'string', 'character varying'];
        return in_array(strtolower($type), $textTypes);
    }
}
