<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;
use Carbon\Carbon;

class UpdatePendingTransactionDates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:update-pending-dates {days=7}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update dates for pending transactions to test auto-cancellation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->argument('days');
        
        $this->info("Fetching transactions with status 'menunggu_pembayaran'...");
        
        $transactions = Transaction::where('order_status', 'menunggu_pembayaran')->get();
        
        if ($transactions->isEmpty()) {
            $this->warn('No transactions found with status "menunggu_pembayaran".');
            return 0;
        }
        
        $this->info("Found {$transactions->count()} transaction(s).");
        
        $updatedCount = 0;
        
        foreach ($transactions as $transaction) {
            $newDate = Carbon::now()->subDays($days);
            
            $this->line("\nUpdating Transaction ID: {$transaction->transaction_id}");
            $this->line("  Old created_at: {$transaction->created_at}");
            $this->line("  Old updated_at: {$transaction->updated_at}");
            $this->line("  Old order_date: {$transaction->order_date}");
            
            $transaction->created_at = $newDate;
            $transaction->updated_at = $newDate;
            $transaction->order_date = $newDate;
            $transaction->save();
            
            $this->line("  New created_at: {$transaction->created_at}");
            $this->line("  New updated_at: {$transaction->updated_at}");
            $this->line("  New order_date: {$transaction->order_date}");
            
            $updatedCount++;
        }
        
        $this->newLine();
        $this->info("✓ Successfully updated {$updatedCount} transaction(s).");
        $this->info("  Dates moved back by {$days} days.");
        
        return 0;
    }
}
