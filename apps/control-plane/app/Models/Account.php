<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['owner_user_id', 'type', 'name', 'status', 'currency'])]
class Account extends Model
{
    use HasFactory;

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_user_id');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('role')->withTimestamps();
    }

    public function wallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function publisherWebsites(): HasMany
    {
        return $this->hasMany(PublisherWebsite::class);
    }

    public function guestPostOrdersAsAdvertiser(): HasMany
    {
        return $this->hasMany(GuestPostOrder::class, 'advertiser_account_id');
    }

    public function guestPostOrdersAsPublisher(): HasMany
    {
        return $this->hasMany(GuestPostOrder::class, 'publisher_account_id');
    }

    public function isPublisher(): bool
    {
        return $this->type === 'publisher';
    }

    public function isAdvertiser(): bool
    {
        return in_array($this->type, ['advertiser', 'agency'], true);
    }

    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }
}
