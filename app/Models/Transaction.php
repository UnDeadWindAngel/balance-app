<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    protected $fillable=[
        'user_id',
        'type',
        'amount',
        'comment',
        'related_user_id',
    ];

    protected $casts=[
        'amount' => 'decimal:2',
    ];

    const TYPE_DEPOSIT = 'deposit';
    const TYPE_WITHDRAW = 'withdraw';
    const TYPE_TRANSFER_OUT = 'transfer_out';
    const TYPE_TRANSFER_IN = 'transfer_in';

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function relatedUser()
    {
        return $this->belongsTo(User::class, 'related_user_id');
    }
}
