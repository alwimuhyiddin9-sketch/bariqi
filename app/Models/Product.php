<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $guarded = [];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function saleItems()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

    /** Total stok termasuk varian */
    public function getComputedStockAttribute()
    {
        return $this->has_variants
            ? $this->variants->sum('stock')
            : $this->stock;
    }

    /** Nilai persediaan (modal x stok) */
    public function getInventoryValueAttribute()
    {
        return $this->has_variants
            ? $this->variants->reduce(fn ($c, $v) => $c + ($v->stock * $v->cost_price), 0)
            : $this->stock * $this->cost_price;
    }
}
