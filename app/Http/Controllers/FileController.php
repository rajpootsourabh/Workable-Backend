<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class FileController extends Controller
{
    /**
     * Retrieve a file by its name.
     *
     * @param  string  $fileName
     * @return \Illuminate\Http\Response
     */
    public function getFileByName($fileName)
    {
        // Check if the file exists in the 'local' disk
        if (!Storage::disk('local')->exists($fileName)) {
            abort(404); // File not found
        }

        // Get the file's contents
        $file = Storage::disk('local')->get($fileName);

        // Determine the MIME type of the file
        $mimeType = Storage::disk('local')->mimeType($fileName);

        // Return the file as a response with the correct MIME type
        return Response::make($file, 200)->header("Content-Type", $mimeType);
    }
}
