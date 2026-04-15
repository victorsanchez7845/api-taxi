<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PercentageTariff extends Model {
  use HasFactory;
  protected $table = 'percentage_tariff';
  public $timestamps = true;

  protected $fillable = [
    'percentage_value',
    'is_active',
    'created_at',
    'updated_at'
  ];

}