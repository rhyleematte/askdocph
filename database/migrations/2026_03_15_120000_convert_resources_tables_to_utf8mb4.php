<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ConvertResourcesTablesToUtf8mb4 extends Migration
{
    /**
     * Ensure resources tables can store 4-byte unicode (emoji) safely.
     *
     * This is a no-op for non-MySQL drivers.
     *
     * @return void
     */
    public function up()
    {
        $connection = DB::connection();
        if ($connection->getDriverName() !== 'mysql') {
            return;
        }

        $charset = $connection->getConfig('charset') ?: 'utf8mb4';
        $collation = $connection->getConfig('collation') ?: 'utf8mb4_unicode_ci';

        foreach (['resources', 'resource_bodies'] as $table) {
            if (!Schema::hasTable($table)) {
                continue;
            }

            DB::statement(sprintf(
                'ALTER TABLE `%s` CONVERT TO CHARACTER SET %s COLLATE %s',
                $table
                , $charset
                , $collation
            ));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Charset conversions are intentionally not reversed.
    }
}
