<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CostCenter extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'description',
    ];

    /**
     * The users that belong to the cost center.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * Get the budget entries for the cost center.
     */
    public function budgetEntries(): HasMany
    {
        return $this->hasMany(BudgetEntry::class);
    }

    /**
     * Get the forecast entries for the cost center.
     */
    public function forecastEntries(): HasMany
    {
        return $this->hasMany(ForecastEntry::class);
    }
}
