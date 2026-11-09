<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public const LOW_STOCK_THRESHOLD = 10;

    protected $fillable = [
        'name',
        'qty',
        'price',
        'description',
        'image_path',
    ];

    public function isOutOfStock(): bool
    {
        return $this->qty <= 0;
    }

    public function isLowStock(): bool
    {
        return $this->qty > 0 && $this->qty <= self::LOW_STOCK_THRESHOLD;
    }

    public function stockStatusTone(): string
    {
        if ($this->isOutOfStock()) {
            return 'danger';
        }

        if ($this->isLowStock()) {
            return 'warning';
        }

        return 'success';
    }

    public function stockStatusLabel(): string
    {
        if ($this->isOutOfStock()) {
            return 'Out of stock';
        }

        if ($this->isLowStock()) {
            return 'Low stock';
        }

        return 'In stock';
    }

    public static function inventoryStats(bool $includeValue = false): array
    {
        $stats = self::query()
            ->selectRaw(
                'COUNT(*) as total,
                COALESCE(SUM(qty), 0) as units,
                COALESCE(SUM(CASE WHEN qty <= 0 THEN 1 ELSE 0 END), 0) as out_of_stock,
                COALESCE(SUM(CASE WHEN qty > 0 AND qty <= ? THEN 1 ELSE 0 END), 0) as low_stock',
                [self::LOW_STOCK_THRESHOLD]
            )
            ->firstOrFail();

        $result = [
            'total' => (int) $stats->total,
            'units' => (int) $stats->units,
            'low_stock' => (int) $stats->low_stock,
            'out_of_stock' => (int) $stats->out_of_stock,
            'attention' => (int) $stats->low_stock + (int) $stats->out_of_stock,
        ];

        if ($includeValue) {
            $result['value'] = (float) self::query()
                ->selectRaw('COALESCE(SUM(qty * price), 0) as total_value')
                ->value('total_value');
        }

        return $result;
    }
}
