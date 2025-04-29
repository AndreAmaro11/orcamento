<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        "user_id",
        "cost_center_id",
        "account_id",
        "year",
        "month",
        "entry_type", // 'budget' or 'forecast'
        "comment",
    ];

    /**
     * Get the user who made the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the cost center associated with the comment.
     */
    public function costCenter()
    {
        return $this->belongsTo(CostCenter::class);
    }

    /**
     * Get the account associated with the comment.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
