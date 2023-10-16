<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\Product;
use App\Models\Upload;

class ProcessCsv implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $upload;


    /**
     * Create a new job instance.
     */
    public function __construct($filePath, Upload $upload)
    {
        $this->filePath = $filePath;
        $this->upload = $upload;
    }

    /**
     * Execute the job.
     */
    public function handle()
    {
        $handle = fopen($this->filePath, 'r');
        $headers = fgetcsv($handle);

        $totalRows = count(file($this->filePath)) - 1; // Exclude the header
        $processedRows = 0;

        while ($row = fgetcsv($handle)) {
            $record = array_combine($headers, $row);
            Product::updateOrCreate(
                ['unique_key' => $record['UNIQUE_KEY']],
                [
                    'product_title' => $record['PRODUCT_TITLE'],
                    'product_description' => $record['PRODUCT_DESCRIPTION'],
                    'style_number' => $record['STYLE#'],
                    'sanmar_mainframe_color' => $record['SANMAR_MAINFRAME_COLOR'],
                    'size' => $record['SIZE'],
                    'color_name' => $record['COLOR_NAME'],
                    'piece_price' => $record['PIECE_PRICE']
                ]
            );

            $processedRows++;
    $progress = ($processedRows / $totalRows) * 100;
    $this->upload->update(['progress' => $progress]);
        }

        fclose($handle);

        // $this->upload->status = 'complete';
        // $this->upload->save();
        $this->upload->update(['status' => 'complete', 'progress' => 100]);
    }
}
