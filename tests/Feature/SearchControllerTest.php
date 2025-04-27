<?php

namespace Tests\Feature;

use App\Gateways\ParkAndRideRankerGateway;
use App\Gateways\ParkingSpaceRankerGateway;
use App\Http\Resources\Location;
use App\Models\User;
use App\ParkAndRide;
use App\ParkingSpace;
use App\SearchService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SearchControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testSearchEndpointUsesGatewayRanking()
    {
        // Arrange
        ParkAndRide::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'attraction_name' => 'Test Attraction',
            'location_description' => 'Test Location',
            'minutes_to_destination' => 5,
        ]);

        ParkingSpace::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'space_details' => 'Test Space',
            'city' => 'Test City',
            'street_name' => 'Test Street',
            'no_of_spaces' => 1,
        ]);

        // Mock ParkAndRideRankerGateway
        app()->singleton(ParkAndRideRankerGateway::class, function () {
            $rankerGateway = $this->getMockBuilder(ParkAndRideRankerGateway::class)
                ->disableOriginalConstructor()
                ->getMock();
            $rankerGateway->expects($this->once())
                ->method('rank')
                ->willReturnCallback(function ($items) {
                    // Verify that rank method was called with correct data
                    $this->assertIsArray($items);
                    return $items; // Return items in same order
                });
            return $rankerGateway;
        });

        // Mock ParkingSpaceRankerGateway
        app()->singleton(ParkingSpaceRankerGateway::class, function () {
            $rankerGateway = $this->getMockBuilder(ParkingSpaceRankerGateway::class)
                ->disableOriginalConstructor()
                ->getMock();
            $rankerGateway->expects($this->once())
                ->method('rank')
                ->willReturnCallback(function ($items) {
                    // Verify that rank method was called with correct data
                    $this->assertIsArray($items);
                    return $items; // Return items in same order
                });
            return $rankerGateway;
        });

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Act
        $response = $this->actingAs($user, 'api')
            ->json('GET', '/api/v1/search', [
                'lat' => 0.1,
                'lng' => 0.1,
            ]);

        // Assert
        $response->assertStatus(200);
    }

    public function testSearchResultsAreCached(): void
    {
        // Arrange
        $parkingSpace = ParkAndRide::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'attraction_name' => 'Test Attraction',
            'location_description' => 'Test Location',
            'minutes_to_destination' => 5,
        ]);

        $expectedResponse = Location::collection(collect([$parkingSpace]));
        $coordinates = ['lat' => 0.1, 'lng' => 0.1];
        $cacheKey = "search_results:{$coordinates['lat']}:{$coordinates['lng']}";

        Cache::put($cacheKey, $expectedResponse, now()->addMinutes(5));

        $searchService = $this->mock(SearchService::class);
        $searchService->allows('getBoundingBox')->never();
        $searchService->allows('searchParkingSpaces')->never();
        $searchService->allows('searchParkAndRide')->never();

        $this->mock(ParkAndRideRankerGateway::class)
            ->allows('rank')->never();
        $this->mock(ParkingSpaceRankerGateway::class)
            ->allows('rank')->never();

        $user = User::factory()->create();

        // Act
        $response = $this->actingAs($user, 'api')
            ->json('GET', '/api/v1/search', $coordinates);

        // Assert
        $response->assertStatus(200);
        $this->assertTrue(Cache::has($cacheKey));
        $this->assertEquals(
            Cache::get($cacheKey)->response()->getData(true),
            $response->json()
        );
    }

    public function testSearchEndpointIsHealthy(): void
    {
        // Arrange
        app()->singleton(ParkAndRideRankerGateway::class, function () {
            $rankerGateway = $this->getMockBuilder(ParkAndRideRankerGateway::class)->disableOriginalConstructor()->getMock();
            $rankerGateway->method('rank')->willReturnArgument(0);
            return $rankerGateway;
        });

        app()->singleton(ParkingSpaceRankerGateway::class, function () {
            $rankerGateway = $this->getMockBuilder(ParkingSpaceRankerGateway::class)->disableOriginalConstructor()->getMock();
            $rankerGateway->method('rank')->willReturnArgument(0);
            return $rankerGateway;
        });

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Act
        $response = $this->actingAs($user, 'api')
            ->json('GET', '/api/v1/search', [
                'lat' => 0.1,
                'lng' => 0.1,
            ]);

        // Assert
        $response->assertStatus(200);
    }

    public function testDetailsEndpoint(): void
    {
        // Arrange
        ParkAndRide::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'attraction_name' => 'disneyland',
            'location_description' => 'TCR',
            'minutes_to_destination' => 10,
        ]);

        ParkingSpace::factory()->create([
            'lat' => 0.1,
            'lng' => 0.1,
            'space_details' => 'Driveway off street',
            'city' => 'London',
            'street_name' => 'Oxford Street',
            'no_of_spaces' => 2,
        ]);

        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Act
        $response = $this->actingAs($user, 'api')
            ->json('GET', '/api/v1/details', [
                'lat' => 0.1,
                'lng' => 0.1,
            ]);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals(json_encode([
            'status' => 'success',
            'message' => 'Locations fetched successfully',
            'data' => [
                [
                    "description" => "Park and Ride to disneyland. (approx 10 minutes to destination)",
                    "location_name" => "TCR"
                ],
                [
                    "description" => "Parking space with 2 bays: Driveway off street",
                    "location_name" => "Oxford Street, London"
                ]
            ]
        ]), $response->getContent());
    }

    public function testDetailsEndpointReturnsEmptyResponseWhenNoResultsFound(): void
    {
        // Arrange
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Act
        $response = $this->actingAs($user, 'api')
            ->json('GET', '/api/v1/details', [
                'lat' => 0.1,
                'lng' => 0.1,
            ]);

        // Assert
        $response->assertStatus(200);
        $this->assertEquals(json_encode([
            'status' => 'success',
            'message' => 'Locations fetched successfully',
            'data' => []
        ]), $response->getContent());
    }

    public function testReturnsValidationErrorForInvalidLongitude(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user, 'api')
            ->json('GET', '/api/v1/details', [
                'lat' => 0.1,
                'lng' => 300,  // Invalid longitude -180 to 180
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The lng must be between -180 and 180.',
                'errors' => [
                    'lng' => ['The lng must be between -180 and 180.'],
                ]
            ]);
    }

    public function testReturnsValidationErrorForInvalidLatitude(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $response = $this->actingAs($user, 'api')
            ->json('GET', '/api/v1/details', [
                'lat' => -100, // Invalid latitude -90 to 90
                'lng' => 1.0,
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'message' => 'The lat must be between -90 and 90.',
                'errors' => [
                    'lat' => ['The lat must be between -90 and 90.'],
                ]
            ]);
    }

    public function testDetailsEndpointReturnsErrorForUnauthenticatedUser(): void
    {
        // Act
        $response = $this->json('GET', '/api/v1/details', [
                'lat' => 0.1,
                'lng' => 0.1,
            ]);

        // Assert
        $response->assertStatus(401);
        $this->assertEquals(json_encode([
            'message' => 'Unauthenticated.',
        ]), $response->getContent());
    }
}
