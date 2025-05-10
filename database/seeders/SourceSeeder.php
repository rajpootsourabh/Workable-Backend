<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SourceSeeder extends Seeder
{
    public function run()
    {
        $sources = ['LinkedIn', 'Indeed', 'Referral', 'Company Website', 'Walk-in', 'Email Campaign', 'Careers Page'];

        foreach ($sources as $name) {
            DB::table('sources')->insert([
                'name' => $name,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
