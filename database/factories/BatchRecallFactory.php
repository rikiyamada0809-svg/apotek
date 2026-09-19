<?php

namespace Database\Factories;

use App\Models\Batch;
use App\Models\BatchRecall;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BatchRecall>
 */
class BatchRecallFactory extends Factory
{
    public function definition(): array
    {
        return [
            'batch_id' => Batch::factory(),
            'recall_number' => 'RCL-'.date('Ymd').'-'.fake()->unique()->numberBetween(1000, 9999),
            'recall_source' => 'BPOM RI',
            'recall_reason' => 'Peringatan cemaran etilen glikol melebihi ambang batas',
            'recall_date' => now()->toDateString(),
            'affected_qty' => 100,
            'sold_qty' => 30,
            'recovered_qty' => 70,
            'status' => 'quarantined',
            'action_taken' => 'Isolasi di rak karantina dan surat konfirmasi ke supplier',
            'notes' => 'Surat edaran nomor HK.02.02/123/2026',
            'created_by' => User::factory(),
        ];
    }
}
