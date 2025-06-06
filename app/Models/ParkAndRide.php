<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ParkAndRide
 *
 * @property int $id
 * @property string $name
 * @property float $lat
 * @property float $lng
 * @property string $attraction_name
 * @property string $location_description
 * @property int $minutes_to_destination
 * @property-read User $owner
 */
class ParkAndRide extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'lat',
        'lng',
        'attraction_name',
        'location_description',
        'minutes_to_destination',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
