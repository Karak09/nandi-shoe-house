<?php

namespace App\Http\Controllers\Offline\Colour;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Models\Colour\Colour;

class ColourController extends Controller
{
    public function index()
    {
        $colours = Colour::where('is_deleted', false)
            ->orderBy('id', 'desc')
            ->get();

        $colours->map(function ($c) {
            $c->encrypted_id = Crypt::encryptString($c->id);
            return $c;
        });

        return view('Offline.Colour.colour', compact('colours'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'colour_name' => 'required|string|max:120',
            'colour_id'   => 'required|string|max:50|unique:colour_masters,colour_id,NULL,id,is_deleted,0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $data = $request->only(['colour_name', 'colour_id']);
            $data['is_active'] = $request->has('is_active') ? $request->is_active : true;
            $data['is_deleted'] = false;

            Colour::create($data);

            return response()->json(['status' => 'success', 'message' => 'Colour added successfully!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Failed to add colour: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $encrypted_id)
    {
        $id = Crypt::decryptString($encrypted_id);

        $validator = Validator::make($request->all(), [
            'colour_name' => 'required|string|max:120',
            'colour_id'   => 'required|string|max:50|unique:colour_masters,colour_id,' . $id . ',id,is_deleted,0',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
        }

        try {
            $colour = Colour::findOrFail($id);
            $data = $request->only(['colour_name', 'colour_id']);
            $data['is_active'] = $request->has('is_active') ? $request->is_active : $colour->is_active;

            $colour->update($data);

            return response()->json(['status' => 'success', 'message' => 'Colour updated successfully!']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Update failed: ' . $e->getMessage()], 500);
        }
    }

    public function destroy($encrypted_id)
    {
        try {
            $id = Crypt::decryptString($encrypted_id);
            Colour::findOrFail($id)->update(['is_deleted' => true, 'is_active' => false]);
            return response()->json(['status' => 'success', 'message' => 'Colour deleted successfully.']);
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => 'Deletion failed.'], 500);
        }
    }
}
