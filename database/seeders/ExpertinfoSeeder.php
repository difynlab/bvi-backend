<?php

namespace Database\Seeders;

use App\Models\ExpertInfo;
use Illuminate\Database\Seeder;

class ExpertInfoSeeder extends Seeder
{
    public function run()
    {
        $records = [
            [
                'id' => 1
            ]
        ];

        foreach($records as $record) {
            ExpertInfo::create($record);
        }
    }
}
