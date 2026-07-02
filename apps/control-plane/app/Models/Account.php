<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['owner_user_id', 'type', 'can_buy', 'can_sell_inventory', 'can_sell_services', 'name', 'status', 'currency'])]
class Account extends Model
{
    use HasFactory;

    protected $casts = [
        'can_buy' => 'boolean',
        'can_sell_inventory' => 'boolean',
        'can_sell_services' => 'boolean',
    ];

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

    public function agencyServices(): HasMany
    {
        return $this->hasMany(AgencyService::class, 'agency_account_id');
    }

    public function agencyServiceOrdersAsClient(): HasMany
    {
        return $this->hasMany(AgencyServiceOrder::class, 'client_account_id');
    }

    public function agencyServiceOrdersAsAgency(): HasMany
    {
        return $this->hasMany(AgencyServiceOrder::class, 'agency_account_id');
    }

    public function isPublisher(): bool
    {
        return $this->canSellInventory();
    }

    public function isAdvertiser(): bool
    {
        return $this->canBuy();
    }

    public function isAgency(): bool
    {
        return $this->canSellServices();
    }

    public function canBuyAgencyServices(): bool
    {
        return $this->canBuy();
    }

    public function isAdmin(): bool
    {
        return $this->type === 'admin';
    }

    public function canBuy(): bool
    {
        return $this->isAdmin() || $this->can_buy;
    }

    public function canSellInventory(): bool
    {
        return $this->isAdmin() || $this->can_sell_inventory;
    }

    public function canSellServices(): bool
    {
        return $this->isAdmin() || $this->can_sell_services;
    }

    public function capabilityLabels(): array
    {
        $labels = [];

        if ($this->canBuy()) {
            $labels[] = 'Buyer';
        }

        if ($this->canSellInventory()) {
            $labels[] = 'Publisher';
        }

        if ($this->canSellServices()) {
            $labels[] = 'Agency';
        }

        return $labels;
    }
}
