<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'kode_customer',
        'nama',
        'telepon',
        'email',
        'alamat',
        'status',
        'kategori',
        'tarif_default',
    ];

    protected $casts = [
        'tarif_default' => 'decimal:2',
    ];

    public function worksheetItems()
    {
        return $this->hasMany(WorksheetItem::class);
    }

    public function worksheets()
    {
        return $this->hasManyThrough(Worksheet::class, WorksheetItem::class, 'customer_id', 'id', 'id', 'worksheet_id')->distinct();
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function getSisaHutangAttribute(): float
    {
        return (float) $this->invoices()
            ->where('status_pembayaran', '!=', 'lunas')
            ->sum('sisa_piutang');
    }

    public function getKartuPiutangAttribute(): array
    {
        $invoices = $this->invoices()->with('worksheetItems')->get()->map(function ($inv) {
            return [
                'id' => $inv->id,
                'tanggal' => $inv->tanggal,
                'created_at' => $inv->created_at,
                'tipe' => 'invoice',
                'tipe_label' => 'Nota Penagihan',
                'nomor_referensi' => $inv->nomor_nota,
                'keterangan' => 'Penerbitan Nota ' . $inv->nomor_nota . ' (' . $inv->worksheetItems->count() . ' muatan kargo)',
                'debit' => (float) $inv->total_nota,
                'kredit' => 0.0,
                'url' => route('invoices.show', $inv),
                'status' => $inv->status_pembayaran,
            ];
        });

        $payments = $this->payments()->get()->map(function ($pay) {
            return [
                'id' => $pay->id,
                'tanggal' => $pay->tanggal,
                'created_at' => $pay->created_at,
                'tipe' => 'payment',
                'tipe_label' => 'Pembayaran Masuk',
                'nomor_referensi' => $pay->nomor_pembayaran,
                'keterangan' => 'Penerimaan ' . strtoupper($pay->metode) . ($pay->catatan ? ' (' . $pay->catatan . ')' : ''),
                'debit' => 0.0,
                'kredit' => (float) $pay->jumlah,
                'url' => route('payments.show', $pay),
                'status' => 'lunas',
            ];
        });

        $merged = $invoices->concat($payments)->sort(function ($a, $b) {
            if ($a['tanggal']->equalTo($b['tanggal'])) {
                return $a['created_at']->timestamp <=> $b['created_at']->timestamp;
            }
            return $a['tanggal']->timestamp <=> $b['tanggal']->timestamp;
        });

        $runningBalance = 0;
        $ledger = [];
        foreach ($merged as $item) {
            $runningBalance += ($item['debit'] - $item['kredit']);
            $item['saldo'] = $runningBalance;
            $ledger[] = $item;
        }

        return $ledger;
    }

    public static function generateCode(): string
    {
        $last = self::latest('id')->first();
        $nextNumber = $last ? ($last->id + 1) : 1;
        return 'CUST-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
