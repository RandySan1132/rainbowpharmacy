<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BarCodeData extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_name',
        'image',
        'bar_code',
        'created_at',
        'updated_at',
        'discount',
        'price',
        'description',
        'purchase_id',
        'supplier_id',
        'shelf',
        'category_id',
        'cost_price',
        'in_stock',
        'stock_notified_at',
        'pill_amount', // Ensure this line is present
        'price_per_pill',
        'sale_by_pill',
    ];

    protected $casts = [
        'in_stock' => 'integer', // Add this line to cast in_stock as an integer
        // ...existing casts...
    ];

    // If a product has a single related purchase, use `hasOne`
    public function purchase()
    {
        return $this->hasOne(Purchase::class, 'bar_code_id');
    }

    // Or if multiple purchases exist, use `hasMany`
    public function purchases()
    {
        return $this->hasMany(Purchase::class, 'bar_code_id');
    }

    public function getTotalLeftoverPillsAttribute()
    {
        return $this->purchases->sum('leftover_pills');
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'supplier_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'bar_code_data_category', 'bar_code_data_id', 'category_id');
    }
}
