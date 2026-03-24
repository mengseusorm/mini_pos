<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });

        // Seed defaults
        $defaults = [
            ['key' => 'site_name',    'value' => 'Mini POS'],
            ['key' => 'theme_color',  'value' => 'zinc'],
            ['key' => 'site_description', 'value' => 'Point of Sale System'],
        ];
        DB::table('settings')->insert(
            array_map(fn($r) => array_merge($r, ['created_at' => now(), 'updated_at' => now()]), $defaults)
        );
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};

