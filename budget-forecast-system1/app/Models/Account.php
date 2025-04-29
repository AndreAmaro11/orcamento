<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Account extends Model
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
        // 'parent_id', // Uncomment if hierarchy is implemented
    ];

    /**
     * Get the budget entries for the account.
     */
    public function budgetEntries(): HasMany
    {
        return $this->hasMany(BudgetEntry::class);
    }

    /**
     * Get the forecast entries for the account.
     */
    public function forecastEntries(): HasMany
    {
        return $this->hasMany(ForecastEntry::class);
    }

    // Optional: Define relationship for hierarchy if implemented
    // public function parent()
    // {
    //     return $this->belongsTo(Account::class, 'parent_id');
    // }

    // public function children()
    // {
    //     return $this->hasMany(Account::class, 'parent_id');
    // }
}
