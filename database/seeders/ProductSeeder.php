<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Services\ActivityLogger;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    /**
     * Seed the products table with sample catalog data.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Wireless Ergonomic Mouse',
                'qty' => 148,
                'price' => 34.99,
                'description' => 'Silent-click wireless mouse with adjustable DPI and a contoured shape for all-day comfort.',
            ],
            [
                'name' => 'Mechanical Keyboard',
                'qty' => 62,
                'price' => 89.50,
                'description' => 'Compact mechanical keyboard with hot-swappable switches and per-key backlighting.',
            ],
            [
                'name' => 'USB-C Docking Station',
                'qty' => 27,
                'price' => 129.00,
                'description' => 'Dual-display docking station with 100W power delivery, Ethernet and SD card reader.',
            ],
            [
                'name' => '27" QHD Monitor',
                'qty' => 14,
                'price' => 279.99,
                'description' => '27-inch 1440p IPS monitor with 75Hz refresh rate and height-adjustable stand.',
            ],
            [
                'name' => 'Laptop Stand',
                'qty' => 4,
                'price' => 44.00,
                'description' => 'Aluminium riser that lifts your laptop to eye level and improves airflow.',
            ],
            [
                'name' => 'Noise-Cancelling Headset',
                'qty' => 3,
                'price' => 159.95,
                'description' => 'Over-ear headset with active noise cancellation and a retractable boom microphone.',
            ],
            [
                'name' => 'Webcam 1080p',
                'qty' => 0,
                'price' => 59.00,
                'description' => 'Full HD webcam with auto light correction and a privacy shutter.',
            ],
            [
                'name' => 'Desk Mat XL',
                'qty' => 91,
                'price' => 24.75,
                'description' => 'Stitched-edge desk mat that cushions your wrists and protects the desk surface.',
            ],
            [
                'name' => 'Monitor Light Bar',
                'qty' => 2,
                'price' => 49.90,
                'description' => 'Asymmetric monitor light bar with stepless dimming and no screen glare.',
            ],
            [
                'name' => 'Cable Management Kit',
                'qty' => 75,
                'price' => 18.25,
                'description' => 'Reusable sleeves, clips and ties to keep desk cables tidy and out of the way.',
            ],
            [
                'name' => 'Ergonomic Office Chair',
                'qty' => 21,
                'price' => 349.00,
                'description' => 'Breathable mesh chair with adjustable lumbar support, armrests and seat depth.',
            ],
            [
                'name' => 'Portable SSD 1TB',
                'qty' => 0,
                'price' => 109.99,
                'description' => 'Shock-resistant 1TB portable SSD with 1050MB/s read speeds over USB 3.2.',
            ],
        ];

        $activityLogger = app(ActivityLogger::class);

        foreach ($products as $productData) {
            if (Product::query()->where('name', $productData['name'])->exists()) {
                continue;
            }

            DB::transaction(function () use ($productData, $activityLogger): void {
                $product = new Product;
                $product->fill($productData);
                $product->save();

                $activityLogger->log(null, 'product.created', $product, [
                    'after' => [
                        'name' => $product->name,
                        'qty' => $product->qty,
                        'price' => (string) $product->price,
                        'description' => $product->description,
                    ],
                ]);
            });
        }
    }
}
