<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'advertiser_account_id',
    'publisher_account_id',
    'publisher_website_id',
    'amount_cents',
    'currency',
    'status',
    'target_url',
    'anchor_text',
    'article_title',
    'content_requirements',
    'published_url',
    'publisher_notes',
    'submitted_at',
    'approved_at',
    'approved_by_account_id',
    'due_at',
])]
class GuestPostOrder extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'amount_cents' => 'integer',
            'due_at' => 'datetime',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function advertiserAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'advertiser_account_id');
    }

    public function publisherAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'publisher_account_id');
    }

    public function website(): BelongsTo
    {
        return $this->belongsTo(PublisherWebsite::class, 'publisher_website_id');
    }

    public function approvedByAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'approved_by_account_id');
    }

    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function formattedAmount(): string
    {
        return sprintf('$%s', number_format($this->amount_cents / 100, 2));
    }
}
