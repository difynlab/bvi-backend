<?php

namespace Database\Seeders;

use App\Models\ImportantInfo;
use Illuminate\Database\Seeder;

class ImportantInfoSeeder extends Seeder
{
    public function run()
    {
        $records = [
            [
                'id' => 1
            ]
        ];

        foreach($records as $record) {
            ImportantInfo::create($record);
        }
    }
}
