<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessCsvUpload;
use App\Models\FileUpload;
use Illuminate\Http\Request;

class FileUploadController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('file');
        $filename = $file->store('uploads');

        $fileUpload = FileUpload::create([
            'filename' => $filename,
            'status' => 'pending',
        ]);

        ProcessCsvUpload::dispatch($fileUpload);

        return response()->json(['message' => 'File uploaded successfully', 'id' => $fileUpload->id]);
    }

    public function index()
    {
        $uploads = FileUpload::latest()->get();
        return response()->json($uploads);
    }

    public function status($id)
    {
        $upload = FileUpload::findOrFail($id);
        return response()->json(['status' => $upload->status]);
    }
}