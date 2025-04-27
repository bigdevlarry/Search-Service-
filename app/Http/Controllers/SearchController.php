<?php

namespace App\Http\Controllers;

use App\Gateways\ParkAndRideRankerGateway;
use App\Gateways\ParkingSpaceRankerGateway;
use App\Http\DTO\ParkAndRide;
use App\Http\DTO\ParkingSpace;
use App\Http\Requests\CoordinatesRequest;
use App\Http\Resources\Location;
use App\Services\ParkAndRideService;
use App\Services\ParkingSpaceService;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function __construct(
        private readonly ParkingSpaceService       $parkingSpaceService,
        private readonly ParkAndRideService        $parkAndRideService,
        private readonly SearchService             $searchService,
        private readonly ParkAndRideRankerGateway  $parkAndRideGateway,
        private readonly ParkingSpaceRankerGateway $parkingSpaceGateway,
    ) {}

    public function index(CoordinatesRequest $request): JsonResponse
    {
        $cacheKey = "search_results:{$request->lat}:{$request->lng}";

        return Cache::remember($cacheKey, 300, function () use ($request)
        {
            $boundingBox = $this->searchService->getBoundingBox($request->lat, $request->lng, 5);

            $parkingSpaces = $this->parkingSpaceService->searchParkingSpaces($boundingBox);
            $rankedParkingSpaces = $this->parkingSpaceGateway->rank($parkingSpaces);

            $parkAndRide = $this->parkAndRideService->searchParkAndRide($boundingBox);
            $rankedParkAndRide = $this->parkAndRideGateway->rank($parkAndRide);

            $resultArray = array_merge($rankedParkAndRide, $rankedParkingSpaces);

            return response()->json([
                'status' => 'success',
                'message' => 'Locations fetched successfully',
                'data' => Location::collection(collect($resultArray)),
            ], 200);
        });
    }

    public function details(CoordinatesRequest $request): JsonResponse
    {
        $boundingBox = $this->searchService->getBoundingBox($request->lat, $request->lng, 5);
        $results = collect($this->parkAndRideService->searchParkAndRide($boundingBox))
            ->merge($this->parkingSpaceService->searchParkingSpaces($boundingBox))
            ->toArray();

        if (empty($results)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Locations fetched successfully',
                'data' => [],
            ], 200);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Locations fetched successfully',
            'data' => $this->formatLocations($results),
        ], 200);
    }

    private function formatLocations(array $results): array
    {
        return collect($results)->map(function ($res) {
            if (isset($res['attraction_name'])) {
                return (new ParkAndRide(
                    $res['attraction_name'],
                    $res['minutes_to_destination'],
                    $res['location_description'],
                ))->toArray();
            }

            return (new ParkingSpace(
                $res['no_of_spaces'],
                $res['space_details'],
                $res['street_name'],
                $res['city'],
            ))->toArray();
        })->all();
    }
}
