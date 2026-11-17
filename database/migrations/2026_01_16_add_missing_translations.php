<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $translations = [
            ['locale' => 'en', 'group' => 'db', 'key' => 'Please', 'value' => 'Please'],
            ['locale' => 'en', 'group' => 'db', 'key' => 'fill', 'value' => 'fill'],
            ['locale' => 'en', 'group' => 'db', 'key' => 'all required fields. After adding, product will be available in the product list.', 'value' => 'all required fields. After adding, product will be available in the product list.'],
            ['locale' => 'en', 'group' => 'db', 'key' => 'Add Product', 'value' => 'Add Product'],
            ['locale' => 'en', 'group' => 'db', 'key' => 'Profit Margin Type', 'value' => 'Profit Margin Type'],
        ];

        foreach ($translations as $translation) {
            // Check if translation already exists
            $exists = DB::table('translations')
                ->where('locale', $translation['locale'])
                ->where('group', $translation['group'])
                ->where('key', $translation['key'])
                ->exists();

            if (!$exists) {
                DB::table('translations')->insert([
                    'locale' => $translation['locale'],
                    'group' => $translation['group'],
                    'key' => $translation['key'],
                    'value' => $translation['value'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Clear translation cache
        \Illuminate\Support\Facades\Cache::forget('translations_en');
        \Illuminate\Support\Facades\Cache::forget('translations_by_locale');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $keys = [
            'Please',
            'fill',
            'all required fields. After adding, product will be available in the product list.',
            'Add Product',
            'Profit Margin Type',
        ];

        DB::table('translations')
            ->where('locale', 'en')
            ->where('group', 'db')
            ->whereIn('key', $keys)
            ->delete();
    }
};
