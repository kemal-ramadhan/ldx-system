<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Rack;
use App\Models\RackUnit;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class GenerateMissingRackUnits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'rack:generate-missing-units';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate missing RackUnit records for existing racks';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $racks = Rack::all();
        $generated = 0;

        foreach ($racks as $rack) {
            $currentUnits = RackUnit::where('rack_id', $rack->id)->count();
            
            if ($currentUnits < $rack->total_units) {
                $this->info("Rack ID {$rack->id} ({$rack->code}) has {$currentUnits} units, generating missing units to reach {$rack->total_units}.");
                
                $unitsToInsert = [];
                for ($i = $currentUnits + 1; $i <= $rack->total_units; $i++) {
                    $unitsToInsert[] = [
                        'rack_id' => $rack->id,
                        'client_id' => null,
                        'rack_divice_id' => null,
                        'code' => 'UNT-' . Str::upper(Str::random(6)),
                        'unit_number' => $i,
                        'status' => 'empty',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
                
                if (!empty($unitsToInsert)) {
                    RackUnit::insert($unitsToInsert);
                    $generated++;
                    $this->info("Generated " . count($unitsToInsert) . " units for Rack ID {$rack->id}.");
                }
            }
        }

        if ($generated > 0) {
            $this->info("Successfully generated missing units for {$generated} rack(s).");
        } else {
            $this->info("All racks already have their correct number of units. No missing units found.");
        }
    }
}
