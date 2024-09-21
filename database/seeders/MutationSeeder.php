<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Str;

class MutationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('mutations')->insert([
            'item_id' => 4,
            'user_id' => 1,
            'type' => 'addition',
            'amount' => 10,
            'date' => '2024-04-12',
            'description' => 'desc',
        ]);
    }
}
