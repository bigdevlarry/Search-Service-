<?php

namespace App\Gateways;

use App\ThirdParty\ParkAndRide\ParkAndRideSDK;
use App\ThirdParty\ParkAndRide\RankingRequest;
use App\ThirdParty\TimeoutException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ParkAndRideRankerGateway
{
    const TIMEOUT = 3000; // 3000 in ms = 3 sec

    public function __construct(private readonly ParkAndRideSDK $parkAndRide)
    {}

    public function rank(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        try {
            $keyedItems = collect($items)->keyBy('id');

            $rankedResponse = $this->parkAndRide
                ->getRankingResponse(new RankingRequest($keyedItems->keys()->toArray()))
                ->getResult();

            $ranking = collect($rankedResponse)
                ->sortBy('rank')
                ->pluck('park_and_ride_id');

            Log::info('Got ranking: ' . json_encode($ranking));

            $rankedItems = [];
            foreach ($ranking as $rank) {
                $rankedItems[] = $keyedItems[$rank];
            }
            return $rankedItems;
        } catch (TimeoutException $e) {
            Log::error('Parking and Ride ranking service timeout', [
                'items_count' => count($items),
                'timeout' => self::TIMEOUT
            ]);

            return $items;
        } catch (\Exception $e) {
            Log::error('Parking and Ride ranking service error', [
                'error' => $e->getMessage(),
                'items_count' => count($items)
            ]);

            return $items;
        }
    }
}
