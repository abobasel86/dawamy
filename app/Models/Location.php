<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    use HasFactory;
    // الحقول المسموح بتعبئتها بشكل جماعي
    protected $fillable = ['name', 'latitude', 'longitude', 'radius_meters'];
}

?>