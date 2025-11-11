<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run()
    {
        $records = [
            [
                'first_name' => 'BVI',
                'last_name' => 'Admin',
                'email' => 'bvi@gmail.com',
                'password' => bcrypt('secret'),
                'role' => 'admin'
            ],
            [
                'first_name' => 'CRM',
                'last_name' => 'BVI',
                'email' => 'crm@bvifinance.vg',
                'password' => bcrypt('BVI@2025!'),
                'role' => 'admin'
            ],
            [
                'first_name' => 'Zajjith',
                'last_name' => 'Ahmath',
                'email' => 'zajjith@epirco.net',
                'password' => bcrypt('secret'),
                'role' => 'member',
            ],
            [
                'first_name' => 'Kareem',
                'last_name' => 'Ottley',
                'email' => 'KOttley@bvifinance.vg',
                'password' => bcrypt('Kareem@2025!'),
                'role' => 'member',
            ]
        ];

        foreach($records as $record) {
            User::create($record);
        }
    }
}
