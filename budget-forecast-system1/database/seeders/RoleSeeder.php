<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Criar os três papéis conforme requisitos
        Role::create([
            'name' => 'Administrador',
            'slug' => 'admin'
        ]);

        Role::create([
            'name' => 'Editor',
            'slug' => 'editor'
        ]);

        Role::create([
            'name' => 'Visualizador',
            'slug' => 'viewer'
        ]);
    }
}
