<?php

namespace Database\Factories;

use App\Models\WorkforceApplicationDocument;
use App\Models\WorkforceApplication;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkforceApplicationDocument>
 */
class WorkforceApplicationDocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'workforce_application_id' => WorkforceApplication::factory(),
            'uploaded_by' => User::factory(),
            'storage_disk' => 'local',
            'storage_path' => 'workforce-applications/'.Str::uuid().'/supporting-document.pdf',
            'original_name' => 'supporting-document.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'sha256' => hash('sha256', Str::uuid()->toString()),
            'document_type' => 'supporting',
        ];
    }
}
