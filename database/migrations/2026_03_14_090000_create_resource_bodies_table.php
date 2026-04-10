<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CreateResourceBodiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resource_bodies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resource_id')->constrained('resources')->cascadeOnDelete()->unique();
            $table->longText('content')->nullable();
            $table->string('file_path')->nullable();
            $table->string('file_type')->nullable();
            $table->timestamps();

            $table->index(['resource_id'], 'idx_resource_bodies_resource');
        });

        // Backfill existing resources.content/file_* into resource_bodies before dropping columns.
        if (Schema::hasColumn('resources', 'content')) {
            DB::table('resources')
                ->select(['id', 'content', 'file_path', 'file_type', 'created_at', 'updated_at'])
                ->orderBy('id')
                ->chunkById(100, function ($rows) {
                    $insert = [];
                    foreach ($rows as $row) {
                        $insert[] = [
                            'resource_id' => $row->id,
                            'content' => $row->content,
                            'file_path' => $row->file_path,
                            'file_type' => $row->file_type,
                            'created_at' => $row->created_at,
                            'updated_at' => $row->updated_at,
                        ];
                    }

                    if ($insert) {
                        DB::table('resource_bodies')->insert($insert);
                    }
                });
        }

        $columnsToDrop = [];
        foreach (['content', 'file_path', 'file_type'] as $column) {
            if (Schema::hasColumn('resources', $column)) {
                $columnsToDrop[] = $column;
            }
        }

        if ($columnsToDrop) {
            Schema::table('resources', function (Blueprint $table) use ($columnsToDrop) {
                $table->dropColumn($columnsToDrop);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('resources', function (Blueprint $table) {
            if (!Schema::hasColumn('resources', 'content')) {
                $table->longText('content')->nullable()->after('type');
            }
            if (!Schema::hasColumn('resources', 'file_path')) {
                $table->string('file_path')->nullable()->after('content');
            }
            if (!Schema::hasColumn('resources', 'file_type')) {
                $table->string('file_type')->nullable()->after('file_path');
            }
        });

        if (Schema::hasTable('resource_bodies')) {
            DB::table('resource_bodies')
                ->select(['resource_id', 'content', 'file_path', 'file_type'])
                ->orderBy('id')
                ->chunkById(100, function ($rows) {
                    foreach ($rows as $row) {
                        DB::table('resources')
                            ->where('id', $row->resource_id)
                            ->update([
                                'content' => $row->content,
                                'file_path' => $row->file_path,
                                'file_type' => $row->file_type,
                            ]);
                    }
                });
        }

        Schema::dropIfExists('resource_bodies');
    }
}

