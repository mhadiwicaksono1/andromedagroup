<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class IasPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'nomor_transaksi_ias',
        'tanggal',
        'nomor_nota',
        'invoice_id',
        'nomor_smu',
        'worksheet_id',
        'nominal',
        'cashback',
        'metode_bayar',
        'bank_pengirim',
        'nomor_rekening_tujuan',
        'status',
        'catatan',
        'file_referensi',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'nominal' => 'decimal:2',
        'cashback' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function invoices()
    {
        return $this->belongsToMany(Invoice::class, 'ias_payment_invoices', 'ias_payment_id', 'invoice_id')
                    ->withPivot(['nomor_nota', 'nominal'])
                    ->withTimestamps();
    }

    public function worksheet()
    {
        return $this->belongsTo(Worksheet::class, 'nomor_smu', 'nomor_smu');
    }
}
