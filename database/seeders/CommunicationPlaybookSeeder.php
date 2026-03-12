<?php

namespace Database\Seeders;

use App\Models\CommunicationPlaybook;
use Illuminate\Database\Seeder;

class CommunicationPlaybookSeeder extends Seeder
{
    public function run()
    {
        $records = [
            [
                'id' => 1
            ]
        ];

        foreach($records as $record) {
            CommunicationPlaybook::create($record);
        }
    }
}
