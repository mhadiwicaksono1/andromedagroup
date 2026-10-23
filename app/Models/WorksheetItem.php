<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WorksheetItem extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'worksheet_id',
        'customer_id',
        'marking',
        'origin',
        'jumlah_koli',
        'berat_kg',
        'tarif_per_kg',
        'total_biaya',
        'keterangan',
        'status',
        'invoice_id',
    ];

    protected $casts = [
        'jumlah_koli' => 'integer',
        'berat_kg' => 'decimal:2',
        'tarif_per_kg' => 'decimal:2',
        'total_biaya' => 'decimal:2',
    ];

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
