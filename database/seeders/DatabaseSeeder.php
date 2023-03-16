<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $signs = $this->importCSV(base_path("database/data/signs.csv"));
        $data = [];

        foreach ($signs as $sign) {
            $data[] = ['sign' => $sign ];
        }

        DB::table('signs')->insert($data);
    }

    function importCSV($filename, $delimiter = ',') {
        if(!file_exists($filename) || !is_readable($filename))
            return false;

        $header = null;
        $data = [];
        if (($handle = fopen($filename, 'r')) !== false) {
            while (($row = fgetcsv($handle, 100, $delimiter)) !== false){
                if(!$header)
                    $header = $row;
                else
                    $data[] = $row[0];
            }

            fclose($handle);
        }
            
        return $data;
    }
}
