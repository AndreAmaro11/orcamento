<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ForecastEntry extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'cost_center_id',
        'account_id',
        'year',
        'month',
        'value',
    ];

    /**
     * Get the cost center that owns the forecast entry.
     */
    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    /**
     * Get the account that owns the forecast entry.
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }
}
