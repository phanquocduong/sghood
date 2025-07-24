<?php

namespace Database\Seeders;

use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::whereBetween('id', [6, 105])->get();

        foreach ($users as $user) {
            Schedule::factory()
                ->count(rand(1, 5)) // mỗi user có 1 đến 5 lịch
                ->create([
                    'user_id' => $user->id,
                ]);
        }
    }
}
