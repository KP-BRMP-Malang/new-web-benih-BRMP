<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminTransactionController extends Controller
{

    public function index(Request $request)
    {
        $query = Transaction::with(['user', 'transactionItems.product', 'payments']);
        
        // Filter by order status
        if ($request->filled('status')) {
            $query->where('order_status', $request->status);
        }
        
        // Filter by payment status
        if ($request->filled('payment_status')) {
            if ($request->payment_status == 'no_payment') {
                $query->whereDoesntHave('payments');
            } else {
                $query->whereHas('payments', function($q) use ($request) {
                    $q->where('payment_status', $request->payment_status);
                });
            }
        }
        
        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('order_date', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('order_date', '<=', $request->end_date);
        }
        
        $transactions = $query->orderBy('order_date', 'desc')->get();
        return view('admin.transactions.index', compact('transactions'));
    }

    public function show($id)
    {
        $transaction = Transaction::with(['user', 'transactionItems.product', 'payments', 'shippingAddress', 'province', 'regency'])
            ->where('transaction_id', $id)
            ->firstOrFail();
        return view('admin.transactions.show', compact('transaction'));
    }

     
    public function updateTransactionStatus(Request $request, $id)
    {
        $trx = Transaction::findOrFail($id);
        $newStatus = $request->input('status');

        // Jika status berubah ke selesai dan sebelumnya belum selesai, isi done_date
        if ($newStatus === 'selesai' && $trx->order_status !== 'selesai') {
            $trx->done_date = now();
        }
        // Jika status berubah ke selain selesai, kosongkan done_date
        elseif ($newStatus !== 'selesai') {
            $trx->done_date = null;
        }

        if ($newStatus === 'dibatalkan' && $trx->order_status !== 'dibatalkan') {
            // Kembalikan stok produk jika dibatalkan
            foreach ($trx->transactionItems as $item) {
                $product = $item->product;
                if ($product) {
                    $product->stock += $item->quantity;
                    $product->save();
                }
            }
        }

        $trx->order_status = $newStatus;
        $trx->save();
        
        return redirect()->back()->with('success', 'Status transaksi berhasil diperbarui');
    }
    /**
     * Approve payment
     */
    public function approvePayment(Request $request, $paymentId)
    {
        try {
            $payment = Payment::findOrFail($paymentId);
            $transaction = $payment->transaction;
            
            // Validate that payment is in pending status
            if ($payment->payment_status !== 'pending') {
                return redirect()->back()->with('error', 'Pembayaran tidak dapat dikonfirmasi karena status tidak valid.');
            }
            
            // Update payment status
            $payment->payment_status = 'approved';
            $payment->save();
            
            // Update transaction status to diproses
            $transaction->order_status = 'diproses';

            // Jika done_date belum diisi, isi dengan waktu sekarang
            if (is_null($transaction->done_date)) {
                $transaction->done_date = now();
            }

            $transaction->save();
            
            return redirect()->back()->with('success', 'Pembayaran berhasil dikonfirmasi dan pesanan akan diproses');
        } catch (\Exception $e) {
            Log::error('Error approving payment', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengkonfirmasi pembayaran.');
        }
    }
    
    /**
     * Reject payment
     */
    public function rejectPayment(Request $request, $paymentId)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);
        
        try {
            $payment = Payment::findOrFail($paymentId);
            $transaction = $payment->transaction;
            
            // Validate that payment is in pending status
            if ($payment->payment_status !== 'pending') {
                return redirect()->back()->with('error', 'Pembayaran tidak dapat ditolak karena status tidak valid.');
            }
            
            // Update payment status
            $payment->payment_status = 'rejected';
            $payment->rejection_reason = $request->rejection_reason;
            $payment->save();

            // Update transaction status to dibatalkan
            $transaction->order_status = 'dibatalkan';
            $transaction->save();
            
            // Kembalikan stok produk
             foreach ($transaction->transactionItems as $item) {
                $product = $item->product;
                if ($product) {
                    $product->stock += $item->quantity;
                    $product->save();
                }
            }
            
            return redirect()->back()->with('success', 'Pembayaran ditolak.');
        } catch (\Exception $e) {
            Log::error('Error rejecting payment', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menolak pembayaran.');
        }
    }
    
    
    /**
     * Show billing form
     */
    public function showBillingForm($transaction_id)
    {
        $transaction = Transaction::findOrFail($transaction_id);
        return view('admin.transactions.billing', compact('transaction'));
    }

    /**
     * Store billing information
     */
    public function storeBilling(Request $request, $transaction_id)
    {
        $request->validate([
            'billing_code_file' => 'required|file|mimes:jpg,jpeg,png|max:10240',
            'no_rek_ongkir'     => 'required|file|mimes:jpg,jpeg,png|max:10240',
            'total_ongkir'      => 'required|numeric',
        ]);

        $transaction = Transaction::findOrFail($transaction_id);

        // Simpan file billing
        $billingFile = $request->file('billing_code_file')->store('billing_codes', 'public');
        // Simpan file no_rek_ongkir
        $rekOngkirFile = $request->file('no_rek_ongkir')->store('rek_ongkir_files', 'public');

        // Buat payment baru
        Payment::create([
            'transaction_id'    => $transaction->transaction_id,
            'billing_code_file' => $billingFile,
            'no_rek_ongkir'     => $rekOngkirFile,
            'payment_status'    => 'no_payment',
        ]);

        // Update total_ongkir di transaksi
        $transaction->total_ongkir = $request->total_ongkir;
        $transaction->order_status = 'menunggu_pembayaran';
        $transaction->save();

        return redirect()->route('admin.transactions.show', $transaction->transaction_id)->with('success', 'Billing dan ongkir berhasil disimpan.');
    }
    
    public function updateResi(Request $request, $id)
    {
        $request->validate([
            'no_resi' => 'required|string|max:255',
        ]);

        $transaction = Transaction::findOrFail($id);
        $transaction->no_resi = $request->no_resi;
        $transaction->order_status = 'selesai';
        $transaction->save();

        return redirect()->route('admin.transactions.show', $transaction->transaction_id)
            ->with('success', 'Nomor resi berhasil disimpan dan status transaksi diubah menjadi selesai.');
    }

    public function viewInvoice($id)
    {
        $transaction = Transaction::with(['transactionItems.product', 'payments', 'shippingAddress'])
            ->where('transaction_id', $id)
            ->firstOrFail();

        return view('admin.transactions.invoice_pdf', [
            'transaction' => $transaction,
            'isPdf' => false
        ]);
    }

    public function downloadInvoice($id)
    {
        $transaction = Transaction::with(['transactionItems.product', 'payments', 'shippingAddress'])
            ->where('transaction_id', $id)
            ->firstOrFail();

        $pdf = Pdf::loadView('admin.invoice_pdf', [
            'transaction' => $transaction,
            'isPdf' => true
        ]);
        return $pdf->download('kuitansi_'.$transaction->transaction_id.'.pdf');
    }
}