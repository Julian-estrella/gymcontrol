<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->boolean('can_access_admin')->default(false);
            $table->json('modules')->nullable();
            $table->boolean('is_system')->default(false);
            $table->timestamps();
        });

        $modules = array_keys(config('gymcontrol_modules'));

        DB::table('roles')->insert([
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'can_access_admin' => true,
                'modules' => json_encode($modules),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Staff',
                'slug' => 'staff',
                'can_access_admin' => true,
                'modules' => json_encode(['clients', 'trainers', 'classes', 'membership-plans', 'payments']),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Cliente',
                'slug' => 'cliente',
                'can_access_admin' => false,
                'modules' => json_encode([]),
                'is_system' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
