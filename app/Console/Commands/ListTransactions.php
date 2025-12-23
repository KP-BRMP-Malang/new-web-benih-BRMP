<?php

namespace App\Console\Commands;

use App\Models\Transaction;
use Illuminate\Console\Command;

class ListTransactions extends Command
{
    protected $signature = 'transactions:list';
    protected $description = 'List all transactions with their status';

    public function handle()
    {
        $this->info("Database Path: " . config('database.connections.sqlite.database'));
        $this->info("Environment: " . app()->environment());
        $this->newLine();
        
        $total = Transaction::count();
        $this->info("Total Transactions: {$total}");
        
        if ($total > 0) {
            $this->newLine();
            $transactions = Transaction::all(['transaction_id', 'order_status', 'order_date', 'created_at']);
            
            $this->table(
                ['ID', 'Status', 'Order Date', 'Created At'],
                $transactions->map(fn($t) => [
                    $t->transaction_id,
                    $t->order_status,
                    $t->order_date,
                    $t->created_at
                ])
            );
            
            $this->newLine();
            $this->info("Distinct statuses:");
            $statuses = Transaction::distinct()->pluck('order_status');
            foreach ($statuses as $status) {
                $count = Transaction::where('order_status', $status)->count();
                $this->line("  - {$status}: {$count}");
            }
        }
        
        return 0;
    }
}
