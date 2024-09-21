<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('items')->insert([
            'name' => 'item' . Str::random(4),
            'code' => Str::random(4),
            'stock' => 0,
            'category_id' => 2,
            'location_id' => 3,
            'description' => 'desc',
        ]);
    }
}
