<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserWallet extends Model
{
    protected $fillable = ['user_id', 'coins', 'total_earned', 'total_spent'];

    public function user() { return $this->belongsTo(User::class); }

    public static function forUser(int $userId): self
    {
        return self::firstOrCreate(['user_id' => $userId], ['coins' => 0, 'total_earned' => 0, 'total_spent' => 0]);
    }

    public function spend(int $amount): bool
    {
        if ($this->coins < $amount) return false;
        $this->decrement('coins', $amount);
        $this->increment('total_spent', $amount);
        return true;
    }

    public function earn(int $amount): void
    {
        $this->increment('coins', $amount);
        $this->increment('total_earned', $amount);
    }
}
