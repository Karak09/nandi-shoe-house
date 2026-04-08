<?php

namespace App\Http\Controllers\Common;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\{DistrictMaster, BlockMaster, MunicipalityMaster,GramPanchayatMaster,
                VillageMaster,PostOfficeMaster,WardMaster};

class CommonController extends Controller
{
    public function uploadBase64Image($base64String, $folderPath)
    {
        if (!$base64String) {
            return null;
        }

        // Split the base64 string
        $image_parts = explode(";base64,", $base64String);
        if (count($image_parts) < 2) return null;

        // 1. Validate Extension
        $image_type_aux = explode("image/", $image_parts[0]);
        $image_type = strtolower($image_type_aux[1]);
        
        $allowedExtensions = ['jpeg', 'jpg', 'png'];
        if (!in_array($image_type, $allowedExtensions)) {
            throw new \Exception("Invalid image format. Only JPG, JPEG, and PNG are allowed.");
        }

        // 2. Validate Size (< 70KB)
        // Calculate size in bytes: (length * 3/4) - padding
        $base64Data = $image_parts[1];
        $padding = substr_count(substr($base64Data, -2), '=');
        $sizeInBytes = (strlen($base64Data) * (3 / 4)) - $padding;
        $sizeInKb = $sizeInBytes / 1024;

        if ($sizeInKb > 70) {
            throw new \Exception("Image size must be below 70KB. Uploaded size: " . round($sizeInKb, 2) . "KB.");
        }

        // 3. Decode and Save
        $image_base64 = base64_decode($base64Data);
        $fileName = Str::uuid() . '.' . $image_type;
        $filePath = $folderPath . '/' . $fileName;

        Storage::disk('public')->put($filePath, $image_base64);

        return $filePath;
    }

    public function getDistricts($state_id)
    {
        $districts = DistrictMaster::where('state_id', $state_id)
            ->where('is_active', true)
            ->get(['id', 'name']); 
        return response()->json($districts);
    }

    public function getBlocks($district_id)
    {        
        $blocks = BlockMaster::where('district_id', $district_id)
            ->where('is_active', true)
            ->get(['id', 'name']);
        return response()->json($blocks);
    }

    public function encryptData($data) { 
        return Crypt::encryptString($data); 
    }

    public function decryptData($encryptedData) {
        try { 
            return Crypt::decryptString($encryptedData); 
        } catch (\Exception $e) { 
            return null; 
        }
    }

    public function getMunicipalities($district_id)
    {
        $data = MunicipalityMaster::where('district_id', $district_id)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return response()->json($data);
    }

    public function getGramPanchayats($block_id)
    {
        $data = GramPanchayatMaster::where('block_id', $block_id)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return response()->json($data);
    }

    public function getVillages($gp_id)
    {
        $data = VillageMaster::where('gram_panchayat_id', $gp_id)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return response()->json($data);
    }


    public function getPostOfficesByVillage($vill_id)
    {
        $village = VillageMaster::find($vill_id);
        if (!$village) return response()->json([]);

        $gp = GramPanchayatMaster::find($village->gram_panchayat_id);
        if (!$gp) return response()->json([]);

        $data = PostOfficeMaster::where('block_id', $gp->block_id)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return response()->json($data);
    }

    public function getWards($municipality_id)
    {
        $data = WardMaster::where('municipality_id', $municipality_id)
            ->where('is_active', true)
            ->get(['id', 'name']);

        return response()->json($data);
    }
}