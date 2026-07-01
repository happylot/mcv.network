<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'account_id',
    'domain',
    'name',
    'niche',
    'language',
    'country',
    'monthly_traffic',
    'domain_rating',
    'domain_authority',
    'guest_post_price_cents',
    'turnaround_days',
    'guidelines',
    'sample_url',
    'status',
])]
class PublisherWebsite extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'monthly_traffic' => 'integer',
            'domain_rating' => 'integer',
            'domain_authority' => 'integer',
            'guest_post_price_cents' => 'integer',
            'turnaround_days' => 'integer',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(GuestPostOrder::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function formattedPrice(): string
    {
        return sprintf('$%s', number_format($this->guest_post_price_cents / 100, 2));
    }
}
