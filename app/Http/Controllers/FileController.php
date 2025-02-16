<?php

namespace App\Http\Controllers;

use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FileController extends Controller
{
    // List all files for the authenticated user
    public function index()
    {
        $files = Auth::user()->files;
        return response()->json($files);
    }

    // Store a new file for the authenticated user
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // Max 10MB
        ]);

        $uploadedFile = $request->file('file');

        $file = new File();
        $file->name = $uploadedFile->getClientOriginalName();
        $file->size = $uploadedFile->getSize();
        $file->user_id = Auth::id();
        $file->save();

        // Store the file in the storage/app/public/files directory
        $uploadedFile->storeAs('files', $file->id . '_' . $file->name);

        return response()->json($file, 201);
    }

    // Delete a file
    public function destroy($id)
    {
        $file = File::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // Delete the file from storage
        \Storage::delete('files/' . $file->id . '_' . $file->name);

        // Delete the file record from the database
        $file->delete();

        return response()->json(null, 204);
    }
}