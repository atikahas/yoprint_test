<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Product;
use App\Models\Upload;
use App\Jobs\ProcessCsv;

class UploadController extends Controller
{
    public function index()
    {
        $uploads = Upload::all(); // Retrieve all upload records from the database
        return view('upload', compact('uploads'));
    }

    public function upload(Request $request)
    {
        // Validate the uploaded file
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        // Retrieve and store the uploaded file
        $file = $request->file('csv_file');
        $filePath = $file->storeAs('uploads', $file->getClientOriginalName(), 'local');

        // Create a record for the upload in the database
        $upload = Upload::create([
            'filename' => $file->getClientOriginalName(),
            'status' => 'pending',
        ]);

        // Dispatch a job to process the uploaded file
        // ProcessCsv::dispatch(storage_path("app/{$filePath}"))->onQueue('processing');
        ProcessCsv::dispatch(storage_path("app/{$filePath}"), $upload);

        // Redirect back with success message
        return back()->with('success', 'File uploaded successfully!');
    }


    protected function processFile($filePath)
    {
        $handle = fopen($filePath, 'r');
        $headers = fgetcsv($handle);

        while ($row = fgetcsv($handle)) {
            $record = array_combine($headers, $row);
            
            // Insert or update record in the database
            Product::updateOrCreate(
                ['unique_key' => $record['UNIQUE_KEY']],
                [
                    'product_title' => $record['PRODUCT_TITLE'],
                    'product_description' => $record['PRODUCT_DESCRIPTION'],
                    'style_number' => $record['STYLE#'],
                    'sanmar_mainframe_color' => $record['SANMAR_MAINFRAME_COLOR'],
                    'color_name' => $record['COLOR_NAME'],
                    'size' => $record['SIZE'],
                    'piece_price' => $record['PIECE_PRICE'],
                ]
            );
        }

        fclose($handle);
    }

    public function progress(Upload $upload) {
        return response()->json(['progress' => $upload->progress]);
    }



}
