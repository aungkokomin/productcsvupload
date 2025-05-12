<?php

namespace App\Jobs;

use App\Models\FileUpload;
use App\Models\Product;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Log\Logger;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProcessCsvUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected FileUpload $fileUpload)
    {
    }

    public function handle(): void
    {
        $this->fileUpload->update(['status' => 'processing']);
    
        $path = Storage::path($this->fileUpload->filename);
        $file = fopen($path, 'r');
    
        // Skip header row
        fgetcsv($file);
    
        $batchSize = 1000; // Adjust this value based on your needs and memory constraints
        $batch = [];
    
        DB::beginTransaction();
    
        try {
            while (($row = fgetcsv($file)) !== false) {
                // Skip the row if all values are empty
                if (empty(array_filter($row))) {
                    continue;
                }
    
                $data = $this->cleanData([
                    'unique_key' => isset($row[0]) ? $row[0] : null,
                    'product_title' => isset($row[1]) ? $row[1] : null,
                    'product_description' => isset($row[2]) ? $row[2] : null,
                    'style' => isset($row[3]) ? $row[3] : null,
                    'available_sizes' => isset($row[4]) ? $row[4] : null,
                    'brand_logo_image' => isset($row[5]) ? $row[5] : null,
                    'thumbnail_image' => isset($row[6]) ? $row[6] : null,
                    'color_swatch_image' => isset($row[7]) ? $row[7] : null,
                    'product_image' => isset($row[8]) ? $row[8] : null,
                    'spec_sheet' => isset($row[9]) ? $row[9] : null,
                    'price_text' => isset($row[10]) ? $row[10] : null,
                    'suggested_price' => isset($row[11]) ? $row[11] : null,
                    'category_name' => isset($row[12]) ? $row[12] : null,
                    'subcategory_name' => isset($row[13]) ? $row[13] : null,
                    'color_name' => isset($row[14]) ? $row[14] : null,
                    'color_square_image' => isset($row[15]) ? $row[15] : null,
                    'color_product_image' => isset($row[16]) ? $row[16] : null,
                    'color_product_image_thumbnail' => isset($row[17]) ? $row[17] : null,
                    'size' => isset($row[18]) ? $row[18] : null,
                    'qty' => isset($row[19]) ? $row[19] : null,
                    'piece_weight' => isset($row[20]) ? $row[20] : null,
                    'piece_price' => isset($row[21]) ? $row[21] : null,
                    'dozens_price' => isset($row[22]) ? $row[22] : null,
                    'case_price' => isset($row[23]) ? $row[23] : null,
                    'price_group' => isset($row[24]) ? $row[24] : null,
                    'case_size' => isset($row[25]) ? $row[25] : null,
                    'inventory_key' => isset($row[26]) ? $row[26] : null,
                    'size_index' => isset($row[27]) ? $row[27] : null,
                    'sanmar_mainframe_color' => isset($row[28]) ? $row[28] : null,
                    'mill' => isset($row[29]) ? $row[29] : null,
                    'product_status' => isset($row[30]) ? $row[30] : null,
                    'companion_styles' => isset($row[31]) ? $row[31] : null,
                    'msrp' => isset($row[32]) ? $row[32] : null,
                    'map_pricing' => isset($row[33]) ? $row[33] : null,
                    'front_model_image_url' => isset($row[34]) ? $row[34] : null,
                    'back_model_image' => isset($row[35]) ? $row[35] : null,
                    'front_flat_image' => isset($row[36]) ? $row[36] : null,
                    'back_flat_image' => isset($row[37]) ? $row[37] : null,
                    'product_measurements' => isset($row[38]) ? $row[38] : null,
                    'pms_color' => isset($row[39]) ? $row[39] : null,
                    'gtin' => isset($row[40]) ? $row[40] : null,
                ]);
    
                $batch[] = $data;
    
                if (count($batch) >= $batchSize) {
                    $this->processBatch($batch);
                    $batch = [];
                }
            }
    
            // Process any remaining records
            if (!empty($batch)) {
                $this->processBatch($batch);
            }
    
            DB::commit();
            fclose($file);
            $this->fileUpload->update(['status' => 'completed']);
        } catch (\Exception $e) {
            Log::error('Error processing CSV file: '.$e->getMessage());
            DB::rollBack();
            fclose($file);
            $this->fileUpload->update(['status' => 'failed']);
            throw $e;
        }
    }

    protected function cleanData(array $data): array
    {
        return array_map(function ($value) {
            // Remove non-UTF-8 characters
            return preg_replace('/[^\x20-\x7E]/u', '', $value);
        }, $data);
    }

    protected function processBatch(array $batch): void
    {
        $uniqueKeys = array_column($batch, 'unique_key');
        $existingProducts = Product::whereIn('unique_key', $uniqueKeys)->get()->keyBy('unique_key');

        $inserts = [];
        $updates = [];

        foreach ($batch as $data) {
            if (isset($existingProducts[$data['unique_key']])) {
                $updates[] = $data;
            } else {
                $inserts[] = $data;
            }
        }

        if (!empty($inserts)) {
            Product::insert($inserts);
        }

        if (!empty($updates)) {
            foreach ($updates as $update) {
                Product::where('unique_key', $update['unique_key'])->update($update);
            }
        }
    }
}