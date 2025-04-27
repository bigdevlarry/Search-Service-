<?php

namespace Tests\Feature;

use App\Gateways\ParkingSpaceRankerGateway;
use App\Models\ParkAndRide;
use App\ThirdParty\ParkingSpaceHttpService;
use App\ThirdParty\TimeoutException;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ParkingSpaceRankerGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function testParkingSpaceRanker(): void
    {
        // Arrange
        $parkingSpace7 = ParkAndRide::factory()->create([
            'id' => 7,
            'lat' => 0.1,
            'lng' => 0.1,
            'name' => 'Test 7',
            'location_description' => 'Test Location',
            'minutes_to_destination' => 5,
        ]);

        $parkingSpace8 = ParkAndRide::factory()->create([
            'id' => 8,
            'lat' => 0.1,
            'lng' => 0.1,
            'name' => 'Test 8',
            'location_description' => 'Test Location',
            'minutes_to_destination' => 5,
        ]);

        $parkingSpace9 = ParkAndRide::factory()->create([
            'id' => 9,
            'lat' => 0.1,
            'lng' => 0.1,
            'name' => 'Test 9',
            'location_description' => 'Test Location',
            'minutes_to_destination' => 5,
        ]);

        $client = $this->mock(ParkingSpaceHttpService::class);
        $client->expects('getRanking')
            ->with(json_encode([7, 8, 9]), ParkingSpaceRankerGateway::TIMEOUT)
            ->andReturn(new \GuzzleHttp\Psr7\Response(200, [], json_encode([7, 8, 9])));

        // Mock the logging
        Log::shouldReceive('info')->twice();

        /** @var ParkingSpaceRankerGateway $gateway */
        $gateway = app(ParkingSpaceRankerGateway::class);

        // Act
        $result = $gateway->rank([$parkingSpace7, $parkingSpace8, $parkingSpace9]);

        // Assert
        $this->assertEquals([$parkingSpace8, $parkingSpace9, $parkingSpace7], $result);
    }

    public function testSingleItemReturnsUnchanged(): void
    {
        // Arrange
        $parkingSpace = ParkAndRide::factory()->create([
            'id' => 7,
            'lat' => 0.1,
            'lng' => 0.1,
            'name' => 'Test 7',
            'location_description' => 'Test Location',
            'minutes_to_destination' => 5,
        ]);

        /** @var ParkingSpaceRankerGateway $gateway */
        $gateway = app(ParkingSpaceRankerGateway::class);

        // Act
        $result = $gateway->rank([$parkingSpace]);

        // Assert
        $this->assertEquals([$parkingSpace], $result);
    }

    public function testParkingSpaceRankerReturnsEmptyWhenNoItemIsProvided(): void
    {
        // Arrange
        /** @var ParkingSpaceHttpService $client */
        $client = $this->mock(ParkingSpaceHttpService::class);

        // The getRanking method should never be called
        $client->shouldNotReceive('getRanking');

        /** @var ParkingSpaceRankerGateway $gateway */
        $gateway = app(ParkingSpaceRankerGateway::class);

        // Act
        $result = $gateway->rank([]);

        // Assert
        $this->assertEmpty($result);
    }

    public function testSlowService(): void
    {
        // Arrange
        $parkingSpace7 = ParkAndRide::factory()->create([
            'id' => 7,
            'lat' => 0.1,
            'lng' => 0.1,
            'name' => 'Test 7',
            'location_description' => 'Test Location',
            'minutes_to_destination' => 5,
        ])->toArray();

        /** @var ParkingSpaceHttpService $client */
        $client = $this->mock(ParkingSpaceHttpService::class);

        $client->expects('getRanking')
            ->with(json_encode([7]), ParkingSpaceRankerGateway::TIMEOUT)
            ->andThrow(new TimeoutException());

        /** @var ParkingSpaceRankerGateway $gateway */
        $gateway = app(ParkingSpaceRankerGateway::class);

        Log::shouldReceive('error')
            ->once()
            ->with('Parking space ranking service timeout', \Mockery::on(function($context) {
                return isset($context['items_count']) && isset($context['timeout']);
            }));

        // Act
        $result = $gateway->rank([$parkingSpace7]);

        // Assert
        $this->assertEquals([$parkingSpace7], $result);
    }

    public function testDefaultExceptionReturnsOriginalOrder(): void
    {
        // Arrange
        $parkingSpace7 = ParkAndRide::factory()->create([
            'id' => 7,
            'lat' => 0.1,
            'lng' => 0.1,
            'name' => 'Test 7',
            'location_description' => 'Test Location',
            'minutes_to_destination' => 5,
        ])->toArray();

        /** @var ParkingSpaceHttpService $client */
        $client = $this->mock(ParkingSpaceHttpService::class);

        $client->expects('getRanking')
            ->with(json_encode([7]), ParkingSpaceRankerGateway::TIMEOUT)
            ->andThrow(new Exception());

        /** @var ParkingSpaceRankerGateway $gateway */
        $gateway = app(ParkingSpaceRankerGateway::class);

        Log::shouldReceive('error')
            ->once()
            ->with('Parking space ranking service error', \Mockery::on(function($context) {
                return isset($context['error']) && isset($context['items_count']);
            }));

        // Act
        $result = $gateway->rank([$parkingSpace7]);

        // Assert
        $this->assertEquals([$parkingSpace7], $result);
    }
}
