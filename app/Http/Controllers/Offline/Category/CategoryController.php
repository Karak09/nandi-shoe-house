<?php

namespace App\Http\Controllers\Offline\Category;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Crypt;
use App\Models\Category\Category;

class CategoryController extends Controller
{
    public function index()
    {
        $allCategories = Category::where('is_deleted', false)->get();
        
        // 1. Build the Flat Tree for the Table (Contains Depth and Full Path)
        $categories = $this->buildFlatTree($allCategories);
        
        // 2. Build Dropdown Tree for the Select Form
        $parentCategories = $this->buildFlatTree($allCategories);

        return view('Offline.Category.category', compact('categories', 'parentCategories'));
    }

    // --- THE MAGIC RECURSIVE FUNCTION ---
    // This turns random rows into a perfect hierarchy tree
    private function buildFlatTree($categories, $parentId = null, $depth = 0, $path = '')
    {
        $result = [];
        $children = $categories->where('parent_id', $parentId)->sortBy('name');
        
        foreach ($children as $child) {
            $currentPath = $path === '' ? $child->name : $path . ' > ' . $child->name;
            
            // Attach magic attributes
            $child->depth = $depth;
            $child->full_parent_path = $path === '' ? 'None (Main)' : $path;
            $child->full_path = $currentPath;
            $child->encrypted_id = Crypt::encryptString($child->id);
            
            $result[] = $child;
            
            // Go deeper!
            $result = array_merge($result, $this->buildFlatTree($categories, $child->id, $depth + 1, $currentPath));
        }
        return $result;
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:120',
            'ben_name'  => 'nullable|string|max:120',
            'cat_des'   => 'nullable|string',
            'parent_id' => 'nullable|integer'
        ], ['name.required' => 'Category name is required.']);

        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

        DB::beginTransaction();
        try {
            $data = $request->all();
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
            $data['is_deleted'] = false;

            // Auto Generate Code and ID
            $nextCatId = Category::max('cat_id');
            $data['cat_id'] = $nextCatId && $nextCatId >= 1000 ? $nextCatId + 1 : 1001;

            $prefix = strtoupper(substr(str_replace(' ', '', $request->name), 0, 3));
            $prefix = str_pad($prefix, 3, 'X'); 
            $data['cat_code'] = $prefix . $data['cat_id'];

            Category::create($data);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Category added successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Save failed.'], 500);
        }
    }

    public function update(Request $request, $encrypted_id)
    {
        $id = Crypt::decryptString($encrypted_id);
        $validator = Validator::make($request->all(), [
            'name'      => 'required|string|max:120',
            'parent_id' => 'nullable|integer'
        ]);

        if ($validator->fails()) return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);

        DB::beginTransaction();
        try {
            $category = Category::findOrFail($id);
            $data = $request->only(['name', 'ben_name', 'cat_des', 'parent_id']); 
            $data['is_active'] = $request->has('is_active') ? $request->is_active : false;
            
            if ($data['parent_id'] == $id) $data['parent_id'] = null; 

            $category->update($data);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Updated successfully!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Update failed.'], 500);
        }
    }

    public function destroy($encrypted_id)
    {
        DB::beginTransaction();
        try {
            $id = Crypt::decryptString($encrypted_id);
            
            if (Category::where('parent_id', $id)->where('is_deleted', false)->exists()) {
                return response()->json(['status' => 'error', 'message' => 'Cannot delete! Please delete sub-categories first.'], 403);
            }

            Category::findOrFail($id)->update(['is_deleted' => true, 'is_active' => false]);
            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Deleted securely.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Deletion failed.'], 500);
        }
    }
}