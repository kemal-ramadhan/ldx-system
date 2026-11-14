<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Photos / BAST documents of a device movement (same pattern as rack_images),
 * plus the initial history: one "check_in" per existing device.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('device_movement_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('device_movement_id')->constrained('device_movements')->cascadeOnDelete();
            $table->enum('type', ['photo', 'bast', 'other'])->default('photo');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        /*
        |--------------------------------------------------------------------------
        | Initial history for devices that already exist
        |--------------------------------------------------------------------------
        */
        $devices = DB::table('rack_divices')->whereNull('deleted_at')->orderBy('id')->get();
        $sequence = [];

        foreach ($devices as $device) {
            $occurredAt = $device->installed_at ?? $device->created_at ?? now();
            $period = date('Ym', strtotime($occurredAt));
            $sequence[$period] = ($sequence[$period] ?? 0) + 1;

            DB::table('device_movements')->insert([
                'movement_number' => 'DM-' . $period . '-' . str_pad($sequence[$period], 5, '0', STR_PAD_LEFT),
                'rack_divice_id' => $device->id,
                'type' => 'check_in',
                'occurred_at' => $occurredAt,
                'client_id' => $device->client_id,
                'device_name' => $device->divice_name,
                'device_code' => $device->code,
                'serial_number' => $device->serial_number,
                'model' => $device->model,
                'to_rack_id' => $device->rack_id,
                'to_start_unit' => $device->start_unit,
                'to_end_unit' => $device->end_unit,
                'notes' => 'Data awal (sebelum fitur keluar-masuk device)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('device_movement_attachments');
        DB::table('device_movements')
            ->where('notes', 'Data awal (sebelum fitur keluar-masuk device)')
            ->delete();
    }
};
