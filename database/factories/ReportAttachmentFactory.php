<?php

namespace Database\Factories;

use App\Enums\ReportAttachmentVisibility;
use App\Models\CivicReport;
use App\Models\ReportAttachment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReportAttachment>
 */
class ReportAttachmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'civic_report_id' => CivicReport::factory(),
            'storage_disk' => 'private',
            'storage_path' => 'civic-reports/factory/document.pdf',
            'original_name' => 'evidence.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'sha256' => hash('sha256', 'factory-evidence'),
            'is_public' => false,
            'visibility' => ReportAttachmentVisibility::Reporter,
        ];
    }
}
