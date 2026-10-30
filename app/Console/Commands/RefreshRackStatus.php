<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Rack;

class RefreshRackStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rack:refresh-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate full/active status of racks (all units filled by devices or weight limit 100%)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $changed = 0;

        foreach (Rack::all() as $rack) {
            $before = $rack->status;

            $rack->refreshFullStatus();

            if ($rack->status !== $before) {
                $changed++;
                $this->info("Rack ID {$rack->id} ({$rack->code}): {$before} -> {$rack->status}");
            }
        }

        $this->info("Done. {$changed} rack(s) updated.");
    }
}
