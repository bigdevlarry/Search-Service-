<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class ParkingSpace
 *
 * @property int $id
 * @property string $name
 * @property float $lat
 * @property float $lng
 * @property string $space_details
 * @property string $city
 * @property string $street_name
 * @property int $no_of_spaces
 * @property-read User $owner
 */
class ParkingSpace extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'user_id',
        'lat',
        'lng',
        'space_details',
        'city',
        'street_name',
        'no_of_spaces'
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
