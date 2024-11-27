<?php

use App\Models\Module;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Module::firstOrCreate([
            'module_name' => 'custom_domain',
            'description' => 'Custom Domain',
        ]);
    }

};
