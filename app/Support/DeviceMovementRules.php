<?php

namespace App\Support;

/**
 * Validation rules shared by every keluar-masuk (device movement) form.
 */
class DeviceMovementRules
{
    public static function carrier(): array
    {
        return [
            'carrier_name' => ['nullable', 'string', 'max:255'],
            'carrier_company' => ['nullable', 'string', 'max:255'],
            'carrier_id_number' => ['nullable', 'string', 'max:50'],
            'carrier_phone' => ['nullable', 'string', 'max:30'],
            'visitor_id' => ['nullable', 'integer', 'exists:visitors,id'],
        ];
    }

    public static function attachments(): array
    {
        return [
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['file', 'image', 'mimes:jpeg,png,jpg,webp', 'max:5120'],
            'bast' => ['nullable', 'array', 'max:5'],
            'bast.*' => ['file', 'mimes:pdf,jpeg,png,jpg', 'max:10240'],
        ];
    }

    public static function common(bool $reasonRequired): array
    {
        return [
            'occurred_at' => ['required', 'date'],
            'reason' => [$reasonRequired ? 'required' : 'nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            ...self::carrier(),
            ...self::attachments(),
        ];
    }

    public static function messages(): array
    {
        return [
            'occurred_at.required' => 'Tanggal & jam wajib diisi.',
            'reason.required' => 'Alasan wajib diisi.',
            'photos.*.image' => 'Foto harus berupa gambar (jpg, png, webp).',
            'photos.*.max' => 'Ukuran foto maksimal 5 MB.',
            'bast.*.mimes' => 'Dokumen BAST harus PDF atau gambar.',
            'bast.*.max' => 'Ukuran dokumen BAST maksimal 10 MB.',
        ];
    }
}
