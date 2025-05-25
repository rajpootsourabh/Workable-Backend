<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

class FileController extends Controller
{
    public function show(Request $request, $path)
    {
        // Prevent directory traversal
        if (str_contains($path, '..')) {
            return response()->json(['error' => 'Invalid path'], 400);
        }
    
        try {
            $disk = Storage::disk('private'); // root = storage/app/private
    
            if (!$disk->exists($path)) {
                return response()->json(['error' => 'File not found'], 404);
            }
    
            $fileContent = $disk->get($path);
            $mimeType = $disk->mimeType($path);
            $fileSize = strlen($fileContent);
    
            while (ob_get_level()) {
                ob_end_clean();
            }
    
            // Check query param: ?download=true
            $download = $request->query('download', false);
    
            // If explicitly requested, force download
            if ($download === 'true' || $download === true) {
                $disposition = 'attachment';
            } else {
                // Otherwise, show inline for allowed types
                $inlineTypes = [
                    'image/png', 'image/jpeg', 'image/gif', 'image/webp',
                    'application/pdf',
                ];
                $disposition = in_array($mimeType, $inlineTypes) ? 'inline' : 'attachment';
            }
    
            return response($fileContent, 200)
                ->header('Access-Control-Allow-Origin', 'http://localhost:5173')
                ->header('Access-Control-Allow-Credentials', 'true')
                ->header('Content-Type', $mimeType)
                ->header('Content-Disposition', $disposition . '; filename="' . basename($path) . '"')
                ->header('Content-Length', $fileSize);
    
        } catch (\Exception $e) {
            Log::error("File serving error: " . $e->getMessage());
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
    
}



