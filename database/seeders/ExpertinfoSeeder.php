<?php

namespace Database\Seeders;

use App\Models\Expertinfo;
use Illuminate\Database\Seeder;

class ExpertinfoSeeder extends Seeder
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
