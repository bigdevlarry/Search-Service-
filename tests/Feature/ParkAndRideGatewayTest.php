<?php

namespace Tests\Feature;

use App\Gateways\ParkAndRideRankerGateway;
use App\ThirdParty\ParkAndRide\ParkAndRideSDK;
use App\ThirdParty\ParkAndRide\RankingRequest;
use App\ThirdParty\ParkAndRide\RankingResponse;
use App\ThirdParty\TimeoutException;
use Exception;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ParkAndRideGatewayTest extends TestCase
{
    use RefreshDatabase;

    public function testParkAndRideSDK(): void
    {
        // Arrange
        $parkAndRide1 = [
            'id' => 1,
            'name' => 'Location 1',
            'location_description' => 'Description 1',
            'minutes_to_destination' => 5,
        ];

        $parkAndRide2 = [
            'id' => 2,
            'name' => 'Location 2',
            'location_description' => 'Description 2',
            'minutes_to_destination' => 10,
        ];

        $mockResponse = $this->mock(RankingResponse::class);
        $mockResponse->expects('getResult')
            ->andReturns([
                ['park_and_ride_id' => 2, 'rank' => 0],
                ['park_and_ride_id' => 1, 'rank' => 1],
            ]);

        $mockSdk = $this->mock(ParkAndRideSDK::class);
        $mockSdk->expects('getRankingResponse')
            ->andReturns($mockResponse);

        Log::shouldReceive('info')
            ->once()
            ->with('Got ranking: ' . json_encode([2, 1]));

        /** @var ParkAndRideRankerGateway $gateway */
        $gateway = app(ParkAndRideRankerGateway::class);

        // Act
        $result = $gateway->rank([$parkAndRide1, $parkAndRide2]);

        // Assert
        $this->assertEquals([$parkAndRide2, $parkAndRide1], $result);
    }

    public function testParkAndRideSDKReturnsEmptyWhenNoDataIsProvided(): void
    {
        // Arrange
        $mockSDK = $this->mock(ParkAndRideSDK::class);
        $mockSDK->allows('getRankingResponse')->never();

        /** @var ParkAndRideRankerGateway $gateway */
        $gateway = app(ParkAndRideRankerGateway::class);

        // Act
        $result = $gateway->rank([]);

        // Assert
        $this->assertEmpty($result);
        $this->assertEquals([], $result);
    }

    public function testSlowService(): void
    {
        // Arrange
        $mockSDK = $this->mock(ParkAndRideSDK::class);

        $parkAndRide = [
            'id' => 1,
            'name' => 'Location 1',
            'location_description' => 'Description 1',
            'minutes_to_destination' => 5,
        ];

        $mockResponse = $this->mock(RankingResponse::class);
        $mockResponse->expects('getResult')
            ->andThrow(new TimeoutException());

        $mockSDK->expects('getRankingResponse')
            ->andReturns($mockResponse);

        Log::shouldReceive('error')
            ->once()
            ->with('Parking and Ride ranking service timeout', \Mockery::on(function($context) {
                return isset($context['items_count']) && isset($context['timeout']);
            }));

        /** @var ParkAndRideRankerGateway $gateway */
        $gateway = app(ParkAndRideRankerGateway::class);

        // Act
        $result = $gateway->rank([$parkAndRide]);

        // Assert
        $this->assertEquals([$parkAndRide], $result);
    }

    public function testDefaultExceptionReturnsOriginalOrder(): void
    {
        // Arrange
        $mockSDK = $this->mock(ParkAndRideSDK::class);

        $parkAndRide = [
            'id' => 1,
            'name' => 'Location 1',
            'location_description' => 'Description 1',
            'minutes_to_destination' => 5,
        ];

        $mockResponse = $this->mock(RankingResponse::class);
        $mockResponse->expects('getResult')
            ->andThrow(new Exception());

        $mockSDK->expects('getRankingResponse')
            ->andReturns($mockResponse);

        Log::shouldReceive('error')
            ->once()
            ->with('Parking and Ride ranking service error', \Mockery::on(function($context) {
                return isset($context['error']) && isset($context['items_count']);
            }));

        /** @var ParkAndRideRankerGateway $gateway */
        $gateway = app(ParkAndRideRankerGateway::class);

        // Act
        $result = $gateway->rank([$parkAndRide]);

        // Assert
        $this->assertEquals([$parkAndRide], $result);
    }
}
