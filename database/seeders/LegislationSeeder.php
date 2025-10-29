<?php

namespace Database\Seeders;

use App\Models\Legislation;
use Illuminate\Database\Seeder;

class LegislationSeeder extends Seeder
{
    public function run()
    {
        $records = [
            [
                'id' => 1
            ]
        ];

        foreach($records as $record) {
            Legislation::create($record);
        }
    }
}
