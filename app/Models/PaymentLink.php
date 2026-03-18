<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'reservation_id',
        'link_code',
        'link',
        'currency',
        'amount',
    ];

    public function reservation()
    {
        return $this->belongsTo(Reservations::class);
    }
}
