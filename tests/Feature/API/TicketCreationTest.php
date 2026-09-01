<?php

namespace Tests\Feature\API;

use App\Models\User;
use App\Models\VehicleType;
use App\Models\Ticket;
use App\Models\TicketAttachment;
use App\Models\TicketUpdate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TicketCreationTest extends TestCase
{
    use RefreshDatabase;

    private string $token = 'test-bearer-token-123456';

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.mobile_api.token' => $this->token]);
    }

    public function test_unauthenticated_requests_are_rejected(): void
    {
        $response = $this->postJson('/api/vehicle-support/tickets/create', [
            'email' => 'user@example.com',
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'success' => false,
                'message' => 'Unauthenticated.',
            ]);
    }

    public function test_ticket_creation_validation_errors(): void
    {
        $response = $this->withToken($this->token)
            ->postJson('/api/vehicle-support/tickets/create', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
                'ticket_type',
                'title',
                'description',
                'priority',
                'vehicle_type_id',
                'trip_start_datetime',
                'trip_end_datetime',
                'passenger_count',
                'trip_purpose',
                'trip_locations',
            ]);
    }

    public function test_ticket_creation_success(): void
    {
        $user = User::factory()->create([
            'email' => 'passenger@example.com',
        ]);

        $vehicleType = VehicleType::create([
            'type_name' => 'SUV',
        ]);

        $payload = [
            'email' => 'passenger@example.com',
            'ticket_type' => 'vehicle_support',
            'title' => 'Test Trip to Office',
            'description' => 'Daily commute to office location.',
            'priority' => 'medium',
            'company_id' => 1,
            'project_name' => 'Admin360',
            'vehicle_type_id' => $vehicleType->id,
            'trip_start_datetime' => now()->addHours(2)->toDateTimeString(),
            'trip_end_datetime' => now()->addHours(4)->toDateTimeString(),
            'passenger_count' => 3,
            'trip_purpose' => 'Business Meeting',
            'trip_locations' => [
                [
                    'start' => 'Location A',
                    'end' => 'Location B',
                    'start_lat' => 23.8103,
                    'start_lng' => 90.4125,
                    'end_lat' => 23.8223,
                    'end_lng' => 90.4225,
                ]
            ],
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/vehicle-support/tickets/create', $payload);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => 'Ticket created successfully.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ],
            ]);

        // Assert that the ticket is in the database
        $this->assertDatabaseHas('tickets', [
            'user_id' => $user->id,
            'ticket_type' => 'vehicle_support',
            'title' => 'Test Trip to Office',
            'priority' => 'medium',
            'vehicle_type_id' => $vehicleType->id,
            'passenger_count' => 3,
            'trip_purpose' => 'Business Meeting',
        ]);

        $ticket = Ticket::first();
        $this->assertNotNull($ticket);
        
        // Assert locations format
        $this->assertCount(1, $ticket->trip_location_details);
        $this->assertEquals('Location A', $ticket->trip_location_details[0]['start']);
        $this->assertEquals('Location B', $ticket->trip_location_details[0]['end']);
        $this->assertEquals(1, $ticket->trip_location_details[0]['stop_order']);

        $this->assertCount(1, $ticket->trip_location_coordinates);
        $this->assertEquals(23.8103, $ticket->trip_location_coordinates[0]['start']['latitude']);
        $this->assertEquals(90.4125, $ticket->trip_location_coordinates[0]['start']['longitude']);
        $this->assertEquals(1, $ticket->trip_location_coordinates[0]['stop_order']);

        $formattedLocations = $ticket->formatted_trip_locations;
        $this->assertCount(1, $formattedLocations);
        $this->assertEquals('Location A', $formattedLocations[0]['start']['address']);
        $this->assertEquals(23.8103, $formattedLocations[0]['start']['latitude']);
        $this->assertEquals(90.4125, $formattedLocations[0]['start']['longitude']);

        // Assert ticket updates
        $this->assertDatabaseHas('ticket_updates', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'update_message' => 'Ticket created',
            'update_type' => 'system',
        ]);
    }

    public function test_ticket_creation_with_attachments(): void
    {
        Storage::fake('public');

        $user = User::factory()->create([
            'email' => 'passenger@example.com',
        ]);

        $vehicleType = VehicleType::create([
            'type_name' => 'Sedan',
        ]);

        $file1 = UploadedFile::fake()->image('test_doc1.jpg');
        $file2 = UploadedFile::fake()->create('test_pdf.pdf', 500, 'application/pdf');

        $payload = [
            'email' => 'passenger@example.com',
            'ticket_type' => 'vehicle_support',
            'title' => 'Trip with Attachments',
            'description' => 'Test description.',
            'priority' => 'low',
            'vehicle_type_id' => $vehicleType->id,
            'trip_start_datetime' => now()->addHours(1)->toDateTimeString(),
            'trip_end_datetime' => now()->addHours(2)->toDateTimeString(),
            'passenger_count' => 1,
            'trip_purpose' => 'General',
            'trip_locations' => [
                [
                    'start' => 'Start Point',
                    'end' => 'End Point',
                ]
            ],
            'attachments' => [
                $file1,
                $file2,
            ],
        ];

        $response = $this->withToken($this->token)
            ->postJson('/api/vehicle-support/tickets/create', $payload);

        $response->assertStatus(201);

        $ticket = Ticket::first();
        $this->assertNotNull($ticket);

        // Verify attachments created in database
        $this->assertCount(2, $ticket->attachments);
        $this->assertDatabaseHas('ticket_attachments', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'file_name' => 'test_doc1.jpg',
        ]);
        $this->assertDatabaseHas('ticket_attachments', [
            'ticket_id' => $ticket->id,
            'user_id' => $user->id,
            'file_name' => 'test_pdf.pdf',
        ]);

        // Verify files stored
        $attachment1 = $ticket->attachments()->where('file_name', 'test_doc1.jpg')->first();
        $attachment2 = $ticket->attachments()->where('file_name', 'test_pdf.pdf')->first();

        Storage::disk('public')->assertExists($attachment1->file_path);
        Storage::disk('public')->assertExists($attachment2->file_path);
    }

    public function test_get_dropdowns_success(): void
    {
        \App\Models\Project::create([
            'name' => 'Test Project',
            'status' => 1,
        ]);

        \App\Models\Project::create([
            'name' => 'Inactive Project',
            'status' => 0,
        ]);

        VehicleType::create([
            'type_name' => 'Truck',
        ]);

        $response = $this->withToken($this->token)
            ->getJson('/api/vehicle-support/tickets/dropdowns');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'companies' => [
                    '*' => ['id', 'name']
                ],
                'projects' => [
                    '*' => ['id', 'name']
                ],
                'vehicle_types' => [
                    '*' => ['id', 'name']
                ],
            ]);

        $data = $response->json();
        $this->assertTrue($data['success']);
        
        // Assert only active project is returned
        $projects = collect($data['projects']);
        $this->assertTrue($projects->contains('name', 'Test Project'));
        $this->assertFalse($projects->contains('name', 'Inactive Project'));

        // Assert vehicle types are returned
        $vehicleTypes = collect($data['vehicle_types']);
        $this->assertTrue($vehicleTypes->contains('name', 'Truck'));

        // Assert companies are returned
        $companies = collect($data['companies']);
        $this->assertTrue($companies->contains('name', 'SComm'));
        $this->assertTrue($companies->contains('name', 'STL'));
    }
}
