<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    protected $table = 'transactions';
    protected $primaryKey = 'transaction_id';
    
    protected $casts = [
        'order_date' => 'datetime',
        'estimated_delivery_date' => 'date',
        'done_date' => 'datetime',
    ];
    
    protected $fillable = [
        'user_id',
        'shipping_address_id',
        'recipient_name',
        'recipient_phone',
        'shipping_address',
        'shipping_note',
        'purchase_purpose',
        'province_id',
        'regency_id',
        'total_price',
        'order_status',
        'delivery_method',
        'total_ongkir',
        'no_resi',
        'order_date',
        'done_date',
        'estimated_delivery_date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function shippingAddress()
    {
        return $this->belongsTo(Address::class, 'shipping_address_id', 'address_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class, 'transaction_id', 'transaction_id');
    }

    public function transactionItems()
    {
        return $this->hasMany(TransactionItem::class, 'transaction_id', 'transaction_id');
    }

    public function province()
    {
        return $this->belongsTo(RegProvinces::class, 'province_id', 'id');
    }

    public function regency()
    {
        return $this->belongsTo(RegRegencies::class, 'regency_id', 'id');
    }

    /**
     * Get the display status based on payment and order status
     */
    public function getDisplayStatusAttribute()
    {
        $payment = $this->payments->last();
        $adaKodeBilling = $this->billing_code_file !== null;

        if (!$payment && !$adaKodeBilling && $this->order_status ==='menunggu_kode_billing') {
            return 'Menunggu Kode Billing';
        }

        // If no payment exists, show "Menunggu Pembayaran"
        if (!$payment && $adaKodeBilling && $this->order_status === 'menunggu_pembayaran') {
            return 'Menunggu Pembayaran';
        }

        // If payment is rejected, show "Transaksi Dibatalkan"
        if ($payment && $payment->payment_status === 'rejected') {
            return 'Transaksi Dibatalkan';
        }

        // If payment is pending, show order status
        if ($payment && $payment->payment_status === 'pending' && $this->order_status === 'menunggu_konfirmasi_pembayaran') {
            return 'Menunggu Konfirmasi Pembayaran';
        }

        // If payment is approved, show order status
        if ($payment && $payment->payment_status === 'approved') {
            switch ($this->order_status) {
                case 'diproses':
                    return 'Pesanan sedang diproses';
                case 'selesai':
                    return 'Pesanan telah dikirim';
                case 'dibatalkan':
                    return 'Pesanan telah dibatalkan';
                default:
                    return 'Menunggu Pembayaran';
            }
        }

        return 'Menunggu Pembayaran';
    }
}