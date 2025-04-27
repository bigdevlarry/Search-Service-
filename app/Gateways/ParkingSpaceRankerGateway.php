<?php

namespace App\Gateways;

use App\ThirdParty\ParkAndRide\RankingRequest;
use App\ThirdParty\ParkingSpaceHttpService;
use App\ThirdParty\TimeoutException;
use Illuminate\Support\Facades\Log;

class ParkingSpaceRankerGateway
{
    const TIMEOUT = 3000; // 3000 in ms = 3 sec

    public function __construct(private readonly ParkingSpaceHttpService $httpService)
    {}

    public function rank(array $items): array
    {
        if (empty($items)) {
            return [];
        }

        try {
            $keyedItems = collect($items)->keyBy('id')->all();

            $response = $this->httpService->getRanking(
                json_encode(array_keys($keyedItems)),
                self::TIMEOUT
            );
            $rankedIds = json_decode($response->getBody()->getContents(), true);

            Log::info('Ranked IDs:', ['ranked_ids' => $rankedIds]);

            if (count($rankedIds) > 1) {
                $first = array_shift($rankedIds); // We're removing the first item here [7,8,9] -> [8,9]
                $rankedIds[] = $first;   // Add it to the end [8,9,7]
            }

            Log::info('Sorted IDs:', ['sorted_ranked_ids' => $rankedIds]);

            $rankedItems = [];
            foreach ($rankedIds as $id) {
                $rankedItems[] = $keyedItems[$id];
            }

            return $rankedItems;
        } catch (TimeoutException $e) {
            Log::error('Parking space ranking service timeout', [
                'items_count' => count($items),
                'timeout' => self::TIMEOUT
            ]);

            return $items;
        } catch (\Exception $e) {
            Log::error('Parking space ranking service error', [
                'error' => $e->getMessage(),
                'items_count' => count($items)
            ]);

            return $items;
        }
    }
}
