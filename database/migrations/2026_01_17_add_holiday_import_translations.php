<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $translations = [
            ['locale' => 'en', 'key' => 'db.Import from Excel', 'value' => 'Import from Excel'],
            ['locale' => 'en', 'key' => 'db.Date Format', 'value' => 'Date Format'],
            ['locale' => 'en', 'key' => 'db.Import', 'value' => 'Import'],
            ['locale' => 'en', 'key' => 'db.Holiday imported successfully', 'value' => 'Holiday imported successfully'],
            ['locale' => 'en', 'key' => 'db.records imported', 'value' => 'records imported'],
            ['locale' => 'en', 'key' => 'db.errors occurred', 'value' => 'errors occurred'],
            ['locale' => 'en', 'key' => 'db.Error importing file', 'value' => 'Error importing file'],
            ['locale' => 'en', 'key' => 'db.Import completed with errors', 'value' => 'Import completed with errors'],
            ['locale' => 'en', 'key' => 'db.Download Example CSV', 'value' => 'Download Example CSV'],
        ];

        foreach ($translations as $translation) {
            $exists = DB::table('translations')
                ->where('locale', $translation['locale'])
                ->where('key', $translation['key'])
                ->exists();

            if (!$exists) {
                DB::table('translations')->insert($translation);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $keys = [
            'db.Import from Excel',
            'db.Date Format',
            'db.Import',
            'db.Holiday imported successfully',
            'db.records imported',
            'db.errors occurred',
            'db.Error importing file',
            'db.Import completed with errors',
            'db.Download Example CSV',
        ];

        DB::table('translations')
            ->whereIn('key', $keys)
            ->delete();
    }
};
