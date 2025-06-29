<?php

namespace Database\Seeders;

use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimeOffTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('time_off_types')->insert([
            [
                'name' => 'Sick Leave',
                'description' => 'Leave for medical or health-related issues.',
                'is_paid' => true,
                'requires_attachment' => true,
                'max_days' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Paid Time Off',
                'description' => 'General paid leave for personal use.',
                'is_paid' => true,
                'requires_attachment' => false,
                'max_days' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Unpaid Leave',
                'description' => 'Leave without pay.',
                'is_paid' => false,
                'requires_attachment' => false,
                'max_days' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
