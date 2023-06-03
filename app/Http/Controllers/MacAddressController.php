<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MacAddressController extends Controller
{
    
    public function singleVendorLookup($mac)
    {
        // Known random MACs
        $randomMacs = ['2','6','A','E'];

        // Remove non-aplhanumeric characters
        $aplhaNumMac = preg_replace('/[^a-zA-Z0-9]/', '', $mac);
        //dd($aplhaNumMac);

        // Get the first 3 octets
        $firstThreeMac = substr($aplhaNumMac, 0, 6);
        //dd($firstThreeMac);

        // Get second character for random check
        $secondChar = substr($firstThreeMac, 1, 1);
        
        // Check if MAC is random else lookup the vendor
        if (in_array($secondChar, $randomMacs)) {
            $result[] = [
                'mac_address' => $mac,
                'vendor' => 'Random MAC'
            ];
        } else {
            $vendor = $this->vendorLookup($firstThreeMac);
            $result[] = [
                'mac_address' => $mac,
                'vendor' => $vendor];
        }
        
        return response()->json($result);
    }

    public function multipleVendorsLookup(Request $request)
    {
        $content = $request->getContent();
        $data = json_decode($content, true);
        //dd($data);

        // Check the data exists
        if (!isset($data['mac_addresses']) || empty($data['mac_addresses'])) {
            return response()->json(['error' => 'No MAC Addresses provided']);
        }

        $macAddresses = $data['mac_addresses'];
        // Known random MACs
        $randomMacs = ['2','6','A','E'];
        $result = [];

        foreach ($macAddresses as $macAddress) {

            // Remove non-aplhanumeric characters
            $aplhaNumMac = preg_replace('/[^a-zA-Z0-9]/', '', $macAddress);

            // Get the first 3 octets
            $firstThreeMac = substr($aplhaNumMac, 0, 6);

            // Get second character for random check
            $secondChar = substr($firstThreeMac, 1, 1);

            // Check if MAC is random else lookup the vendor
            if (in_array($secondChar, $randomMacs)) {
                $result[] = [
                    'mac_address' => $macAddress,
                    'vendor' => 'Random MAC'
                ];
            } else {
                $vendor = $this->vendorLookup($firstThreeMac);
                $result[] = [
                    'mac_address' => $macAddress,
                    'vendor' => $vendor];
            }
            
            
        }
        return response()->json($result);


    }

    private function vendorLookup($mac)
    {
        return DB::table('ouidata')->where('assignment', '=', $mac)->value('organisation_name');
    }
}
