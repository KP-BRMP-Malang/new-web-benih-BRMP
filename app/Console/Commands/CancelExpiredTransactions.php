<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CancelExpiredTransactions extends Command
{
    /**
     * The name and signature of the console command.
    *
     * @var string
    */
    protected $signature = 'transactions:cancel-expired';

    /**
     * The console command description.
    *
    * @var string
    */
    protected $description = 'Cancel transactions that have expired payment deadline';
    
    /**
     * Execute the console command.
    */
    public function handle()
    {
        Log::info('Carbon now subDay', ['value' => Carbon::now()->subDay()->toDateTimeString()]);
        try {
            $this->info('Starting to check for expired transactions...');
            
            // Get transactions that are waiting for payment and have expired (1 week after updated_at)
            $expiredTransactions = Transaction::where('order_status', 'menunggu_pembayaran')
            ->where('updated_at', '<=', Carbon::now()->subWeek())
            ->get();
            
            $cancelledCount = 0;
            foreach ($expiredTransactions as $transaction) {
                // Debug: tampilkan semua payment pada transaksi
                Log::info('Payments for transaction', [
                    'transaction_id' => $transaction->transaction_id,
                    'payments' => $transaction->payments()->get()->toArray()
                ]);

                // Batalkan hanya jika ada payment dan status payment-nya 'no_payment'
                $hasNoPaymentStatus = $transaction->payments()
                    ->where('payment_status', 'no_payment')
                    ->exists();

                if ($hasNoPaymentStatus) {
                    $transaction->update([
                        'order_status' => 'dibatalkan'
                    ]);

                    // Update payment status dan rejection_reason
                    $transaction->payments()
                        ->where('payment_status', 'no_payment')
                        ->update([
                            'payment_status' => 'rejected',
                            'rejection_reason' => "Mohon maaf, transaksi Anda dengan nomor transaksi #{$transaction->transaction_id} telah dibatalkan secara otomatis karena sudah melewati batas waktu pembayaran.\n\nAnda dapat membuat pesanan baru untuk melanjutkan pembelian.\n\nTerima kasih."
                        ]);

                    // Kembalikan stok produk
                    foreach ($transaction->transactionItems as $item) {
                        $product = $item->product;
                        if ($product) {
                            $product->stock += $item->quantity;
                            $product->save();
                        }
                    }

                    $cancelledCount++;

                    Log::info('Transaction cancelled due to expired payment (payment status: no_payment)', [
                        'transaction_id' => $transaction->transaction_id,
                        'user_id' => $transaction->user_id,
                        'order_date' => $transaction->order_date,
                        'updated_at' => $transaction->updated_at,
                        'cancelled_at' => Carbon::now()
                    ]);

                    $this->line("Cancelled transaction #{$transaction->transaction_id} for user {$transaction->user->name}");
                }
            }
            
            $this->info("Successfully cancelled {$cancelledCount} expired transactions.");
            
            if ($cancelledCount > 0) {
                Log::info('Expired transactions cancelled', [
                    'count' => $cancelledCount,
                    'executed_at' => Carbon::now()
                ]);
            }
            
            return 0;
        } catch (\Exception $e) {
            Log::error('Error cancelling expired transactions', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            $this->error('Error occurred while cancelling expired transactions: ' . $e->getMessage());
            return 1;
        }
    }
}
