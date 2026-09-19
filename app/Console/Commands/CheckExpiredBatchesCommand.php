<?php

namespace App\Console\Commands;

use App\Models\Batch;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CheckExpiredBatchesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expired:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Periksa semua batch obat dan perbarui status menjadi expired jika melewati tanggal kedaluwarsa';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $today = now()->toDateString();

        $expiredBatches = Batch::where('status', 'active')
            ->where('expiry_date', '<', $today)
            ->get();

        $count = $expiredBatches->count();

        if ($count > 0) {
            foreach ($expiredBatches as $batch) {
                $batch->update(['status' => 'expired']);
            }

            $message = "Berhasil memperbarui {$count} batch obat menjadi status 'expired'.";
            $this->info($message);
            Log::info("[ExpiredCheck] {$message}");
        } else {
            $this->info('Tidak ada batch baru yang melewati tanggal kedaluwarsa hari ini.');
        }

        $expiringIn30Days = Batch::where('status', 'active')
            ->where('current_qty', '>', 0)
            ->whereBetween('expiry_date', [$today, now()->addDays(30)->toDateString()])
            ->count();

        if ($expiringIn30Days > 0) {
            $this->warn("Terdapat {$expiringIn30Days} batch aktif yang akan kedaluwarsa dalam 30 hari ke depan.");
        }

        return self::SUCCESS;
    }
}
