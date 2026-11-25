<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// survey_questions.survey_id and document_questions.document_id were created with foreignIdFor()
// but no constraint, so deleting a survey left its ungrouped questions behind.
return new class extends Migration
{
    /** question table => [foreign key column, parent table] */
    private const array KEYS = [
        'survey_questions' => ['survey_id', 'surveys'],
        'document_questions' => ['document_id', 'documents'],
    ];

    public function up(): void
    {
        foreach (self::KEYS as $tableName => [$column, $parent]) {
            DB::table($tableName)->whereNotIn($column, DB::table($parent)->select('id'))->delete();

            Schema::table($tableName, function (Blueprint $table) use ($column, $parent): void {
                $table->foreign($column)->references('id')->on($parent)->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (self::KEYS as $tableName => [$column]) {
            Schema::table($tableName, function (Blueprint $table) use ($column): void {
                $table->dropForeign([$column]);
            });
        }
    }
};
