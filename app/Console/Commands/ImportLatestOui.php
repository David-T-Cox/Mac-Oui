<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ImportLatestOui extends Command
{
    protected $signature = 'import:ouidata';
    protected $description = 'Import the latest IEEE OUI data into the database';

    public function handle()
    {
        // Fetch latest OUI data
        $response = Http::get('http://standards-oui.ieee.org/oui/oui.csv');

        if ($response->ok()) {

           
            $ouiCsv = $response->body();
            // dd($ouiCsv);

            // Convert CSV to an array of associative arrays
            $ouidata = $this->parseCsv($ouiCsv);

            // Clear existing OUI data
            DB::table('ouidata')->truncate();

            //Chunk data into smaller batches
            $chunks = array_chunk($ouidata, 1000);

            // Insert the new OUI data in batches
            foreach ($chunks as $chunk) {
                DB::table('ouidata')->insert($chunk);
            }
            

            $this->info('Latest OUI data successfully imported.');
         }

        


    }

    private function parseCsv($csvData)
    {

        $lines = explode(PHP_EOL, $csvData);
    //    dd($headers);
 
        $data = [];
    //    Storage::disk('local')->put('ouidata_dump.txt', print_r($lines, true));
    //   dd();
 
       if (empty(end($lines))) {
    //    dd('Final row is empty');
        array_pop($lines);
       }

       $columnNames = ['registry','assignment', 'organisation_name', 'organisation_address'];
       
       $firstLineSkipped = false;
        foreach ($lines as $line) {
            // Skip headers
            if (!$firstLineSkipped) {
                $firstLineSkipped = true;
                continue;
            }

            $values = str_getcsv($line);
            $quotedValues = array_map(function ($value) {
                return trim($value,'"');
            }, $values);
            
            $data[] = array_combine($columnNames, $quotedValues);
        }
      // dd($data);
        return $data;


    }

    
}