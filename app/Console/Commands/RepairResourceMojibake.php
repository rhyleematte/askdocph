<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RepairResourceMojibake extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'resources:repair-mojibake
                            {--apply : Persist fixes to the database}
                            {--limit=0 : Max rows per table to scan (0 = no limit)}
                            {--resource-id=* : Only scan specific resource IDs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Repair mojibake (garbled UTF-8) in existing resources data.';

    public function handle()
    {
        $apply = (bool) $this->option('apply');
        $limit = (int) $this->option('limit');
        $resourceIds = array_values(array_filter((array) $this->option('resource-id')));

        $this->line($apply ? 'Mode: APPLY (will update DB)' : 'Mode: DRY RUN (no DB writes)');
        $this->line('Tip: take a DB backup before applying.');

        $totalScanned = 0;
        $totalChanged = 0;

        $tables = [
            'resources' => [
                'id' => 'id',
                'columns' => ['title', 'description', 'duration_meta', 'hashtags'],
                'filter' => function ($query) use ($resourceIds) {
                    if ($resourceIds) {
                        $query->whereIn('id', $resourceIds);
                    }
                },
            ],
            'resource_bodies' => [
                'id' => 'id',
                'columns' => ['content'],
                'filter' => function ($query) use ($resourceIds) {
                    if ($resourceIds) {
                        $query->whereIn('resource_id', $resourceIds);
                    }
                },
            ],
        ];

        foreach ($tables as $table => $meta) {
            if (!Schema::hasTable($table)) {
                $this->warn("Skipping missing table: {$table}");
                continue;
            }

            $this->info("Scanning: {$table}");

            $idColumn = $meta['id'];
            $columns = $meta['columns'];
            $applyFilter = $meta['filter'];

            $scannedThisTable = 0;
            $changedThisTable = 0;
            $previewShown = 0;

            $baseQuery = DB::table($table)
                ->orderBy($idColumn)
                ->select(array_merge([$idColumn], $columns));

            $applyFilter($baseQuery);

            $baseQuery->chunkById(100, function ($rows) use (
                $table,
                $idColumn,
                $columns,
                $apply,
                $limit,
                &$scannedThisTable,
                &$changedThisTable,
                &$previewShown
            ) {
                foreach ($rows as $row) {
                    $scannedThisTable++;

                    $updates = [];
                    foreach ($columns as $column) {
                        $original = $row->{$column};
                        if ($original === null || $original === '') {
                            continue;
                        }

                        $fixed = $this->fixMojibake($original);
                        if ($fixed !== $original) {
                            $updates[$column] = $fixed;

                            if ($previewShown < 3) {
                                $previewShown++;
                                $this->line("  - {$table}.{$column} id={$row->{$idColumn}}");
                                $this->line('    before: ' . $this->preview($original));
                                $this->line('    after : ' . $this->preview($fixed));
                            }
                        }
                    }

                    if (!empty($updates)) {
                        $changedThisTable++;

                        if ($apply) {
                            DB::table($table)->where($idColumn, $row->{$idColumn})->update($updates);
                        }
                    }

                    if ($limit > 0 && $scannedThisTable >= $limit) {
                        return false;
                    }
                }

                return true;
            }, $idColumn);

            $this->line("Done {$table}: scanned={$scannedThisTable}, changed={$changedThisTable}");

            $totalScanned += $scannedThisTable;
            $totalChanged += $changedThisTable;
        }

        $this->info("All done: scanned={$totalScanned}, changed={$totalChanged}");

        if (!$apply) {
            $this->line('Re-run with `--apply` to persist changes.');
        }

        return 0;
    }

    private function fixMojibake(string $value): string
    {
        $current = $value;

        for ($i = 0; $i < 3; $i++) {
            if ($this->mojibakeScore($current) === 0) {
                break;
            }

            $fixed = $this->tryFixOnce($current);
            if ($fixed === null || $fixed === $current) {
                break;
            }

            $current = $fixed;
        }

        return $current;
    }

    private function tryFixOnce(string $value): ?string
    {
        $asWin1252 = @iconv('UTF-8', 'Windows-1252//IGNORE', $value);
        if ($asWin1252 === false || $asWin1252 === '') {
            return null;
        }

        $roundTrip = @iconv('Windows-1252', 'UTF-8', $asWin1252);
        if ($roundTrip !== $value) {
            // Would drop/alter characters (e.g. CJK text). Skip to avoid data loss.
            return null;
        }

        if (function_exists('mb_check_encoding') && !mb_check_encoding($asWin1252, 'UTF-8')) {
            return null;
        }

        if ($this->mojibakeScore($asWin1252) >= $this->mojibakeScore($value)) {
            return null;
        }

        return $asWin1252;
    }

    private function mojibakeScore(string $value): int
    {
        $score = 0;
        $score += substr_count($value, 'ðŸ') * 3;
        $score += substr_count($value, 'â€') * 2;
        $score += substr_count($value, 'â€™') * 2;
        $score += substr_count($value, 'â€œ') * 2;
        $score += substr_count($value, 'â€�') * 2;
        $score += substr_count($value, 'â€“') * 2;
        $score += substr_count($value, 'â€”') * 2;
        $score += substr_count($value, 'â€¦') * 2;
        $score += substr_count($value, 'Ã');
        $score += substr_count($value, 'Â');

        return $score;
    }

    private function preview(string $value): string
    {
        $oneLine = preg_replace("/\\s+/u", ' ', trim($value));
        if ($oneLine === null) {
            $oneLine = trim($value);
        }

        $limit = 140;
        if (function_exists('mb_strlen') && function_exists('mb_substr')) {
            if (mb_strlen($oneLine) > $limit) {
                return mb_substr($oneLine, 0, $limit) . '…';
            }
            return $oneLine;
        }

        if (strlen($oneLine) > $limit) {
            return substr($oneLine, 0, $limit) . '...';
        }

        return $oneLine;
    }
}
