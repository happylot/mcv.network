<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'agency_account_id',
    'title',
    'category',
    'description',
    'deliverables',
    'base_price_cents',
    'turnaround_days',
    'status',
])]
class AgencyService extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'base_price_cents' => 'integer',
            'turnaround_days' => 'integer',
        ];
    }

    public function agencyAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'agency_account_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(AgencyServiceOrder::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function formattedPrice(): string
    {
        return sprintf('$%s', number_format($this->base_price_cents / 100, 2));
    }
}
