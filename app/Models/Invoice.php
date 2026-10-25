<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'nomor_nota',
        'tipe_nota',
        'tanggal',
        'customer_id',
        'sisa_hutang_sebelumnya',
        'subtotal_lembar_kerja',
        'biaya_adm',
        'penyesuaian_biaya',
        'total_nota',
        'total_tagihan_kumulatif',
        'jumlah_dibayar',
        'sisa_piutang',
        'status_pembayaran',
        'catatan',
        'user_id',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'sisa_hutang_sebelumnya' => 'decimal:2',
        'subtotal_lembar_kerja' => 'decimal:2',
        'biaya_adm' => 'decimal:2',
        'penyesuaian_biaya' => 'decimal:2',
        'total_nota' => 'decimal:2',
        'total_tagihan_kumulatif' => 'decimal:2',
        'jumlah_dibayar' => 'decimal:2',
        'sisa_piutang' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function worksheetItems()
    {
        return $this->hasMany(WorksheetItem::class);
    }

    public function worksheets()
    {
        return $this->hasManyThrough(Worksheet::class, WorksheetItem::class, 'invoice_id', 'id', 'id', 'worksheet_id')->distinct();
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function allocations()
    {
        return $this->hasMany(PaymentAllocation::class);
    }

    public function iasPayments()
    {
        return $this->belongsToMany(IasPayment::class, 'ias_payment_invoices', 'invoice_id', 'ias_payment_id')
                    ->withPivot(['nomor_nota', 'nominal'])
                    ->withTimestamps();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function generateNumber(): string
    {
        $prefix = 'AMG/CB/';
        $last = self::withTrashed()
            ->where('nomor_nota', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        if ($last) {
            $numPart = preg_replace('/[^0-9]/', '', substr($last->nomor_nota, strlen($prefix)));
            $next = ((int) $numPart) + 1;
        } else {
            $next = 1101;
        }

        $generated = $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
        while (self::withTrashed()->where('nomor_nota', $generated)->exists()) {
            $next++;
            $generated = $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
        }

        return $generated;
    }
}
