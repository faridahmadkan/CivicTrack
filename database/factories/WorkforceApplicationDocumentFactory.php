<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkforceApplication;
use App\Models\WorkforceApplicationDocument;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

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
            'storage_disk' => 'private',
            'storage_path' => 'workforce-applications/'.Str::uuid().'/supporting-document.pdf',
            'original_name' => 'supporting-document.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'sha256' => hash('sha256', Str::uuid()->toString()),
            'document_type' => 'supporting',
        ];
    }
}
