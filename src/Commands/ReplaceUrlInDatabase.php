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

        $dbName = DB::getDatabaseName();

        if (!filter_var($oldUrl, FILTER_VALIDATE_URL)) {
            $this->error("Invalid old URL: $oldUrl");
            return Command::FAILURE;
        }

        if (!filter_var($newUrl, FILTER_VALIDATE_URL)) {
            $this->error("Invalid new URL: $newUrl");
            return Command::FAILURE;
        }

        $allTables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        if ($tablesFilter) {
            $invalidTables = array_diff($tablesFilter, $allTables);
            if (!empty($invalidTables)) {
                $this->error('Invalid table(s): ' . implode(', ', $invalidTables));
                return Command::FAILURE;
            }
        }

        $columns = DB::table('information_schema.columns')
            ->where('table_schema', $dbName)
            ->whereIn('data_type', ['varchar', 'text', 'longtext', 'mediumtext', 'char'])
            ->get(['table_name', 'column_name']);

        if ($columnsFilter) {
            $columnFound = false;
            foreach ($columns as $col) {
                if (in_array($col->column_name, $columnsFilter)) {
                    $columnFound = true;
                    break;
                }
            }
            if (!$columnFound) {
                $this->error('None of the specified columns were found in any table.');
                return Command::FAILURE;
            }
        }

        $results = [];

        foreach ($columns as $col) {
            $table = $col->table_name;
            $column = $col->column_name;

            if ($tablesFilter && !in_array($table, $tablesFilter)) {
                continue;
            }

            if ($columnsFilter && !in_array($column, $columnsFilter)) {
                continue;
            }

            if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
                continue;
            }

            try {
                $count = DB::table($table)
                    ->where($column, 'LIKE', "%$oldUrl%")
                    ->count();

                if ($count > 0) {
                    $replaced = $isDryRun ? 0 : DB::table($table)
                        ->where($column, 'LIKE', "%$oldUrl%")
                        ->update([
                            $column => DB::raw("REPLACE(`$column`, '$oldUrl', '$newUrl')")
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
}
