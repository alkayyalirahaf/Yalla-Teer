<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Flight extends Model
{
    protected $fillable = [
        'airline_id',
        'flight_number',
        'origin_airport_id',
        'destination_airport_id',
        'departure_at',
        'arrival_at',
        'stops',
        'status',
        'trip_type',
        'return_flight_id',
        'is_return_leg',
    ];

    protected function casts(): array
    {
        return [
            'departure_at' => 'datetime',
            'arrival_at' => 'datetime',
            'stops' => 'integer',
            'is_return_leg' => 'boolean',
        ];
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class, 'airline_id');
    }

    public function originAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'origin_airport_id');
    }

    public function destinationAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'destination_airport_id');
    }

    public function flightClasses(): HasMany
    {
        return $this->hasMany(FlightClass::class, 'flight_id');
    }

    public function bookingFlights(): HasMany
    {
        return $this->hasMany(BookingFlight::class, 'flight_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class, 'flight_id');
    }

    public function returnFlight(): BelongsTo
    {
        return $this->belongsTo(self::class, 'return_flight_id');
    }

    public function outboundFlight(): HasOne
    {
        return $this->hasOne(self::class, 'return_flight_id');
    }
}
