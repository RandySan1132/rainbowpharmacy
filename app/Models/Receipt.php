<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'date',
        'total_amount',
        'receipt_details',
        'sale_id', // Add this line
    ];

    public function sales()
    {
        // Fetch all sales by shared invoice_id
        return $this->hasMany(Sale::class, 'invoice_id', 'invoice_id');
    }
}
