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

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file',
        ]);

        $uploadedFile = $request->file('file');
        $fileId = $request->header('X-File-ID'); // Unique file ID for chunked uploads
        $fileName = $request->header('X-File-Name'); // Original file name

        // Ensure the file name is not null
        if (is_null($fileName)) {
            return response()->json(['error' => 'File name is required'], 400);
        }

        // Store the chunk in a temporary directory
        $chunkPath = "/file-manager/uploads/{$fileId}";
        if (!file_exists($chunkPath)) {
            mkdir($chunkPath, 0777, true);
        }
        $uploadedFile->move($chunkPath, $request->header('X-Chunk-Index'));

        // Check if all chunks have been uploaded
        $totalChunks = (int) $request->header('X-Total-Chunks');
        $currentChunk = (int) $request->header('X-Chunk-Index') + 1;

        if ($currentChunk === $totalChunks) {
            // Combine chunks into a single file
            $finalFilePath = "/file-manager/uploads/{$fileId}_final";
            $finalFile = fopen($finalFilePath, 'wb');

            for ($i = 0; $i < $totalChunks; $i++) {
                $chunk = fopen("{$chunkPath}/{$i}", 'rb');
                stream_copy_to_stream($chunk, $finalFile);
                fclose($chunk);
            }

            fclose($finalFile);

            // Save the file to the database
            $file = new File();
            $file->name = $fileName; // Use the original file name
            $file->size = filesize($finalFilePath);
            $file->user_id = Auth::id();
            $file->save();

            // Move the final file to the storage directory
            rename($finalFilePath, "/file-manager/files/{$file->id}_{$fileName}");

            // Clean up temporary chunks
            array_map('unlink', glob("{$chunkPath}/*"));
            rmdir($chunkPath);

            return response()->json($file, 201);
        }

        return response()->json(['status' => 'chunk-uploaded']);
    }

    // Delete a file
    public function destroy($id)
    {
        $file = File::where('id', $id)->where('user_id', Auth::id())->firstOrFail();

        // Delete the file from storage
        $filePath = "/file-manager/files/{$file->id}_{$file->name}";
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Delete the file record from the database
        $file->delete();

        return response()->json(null, 204);
    }
}