<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResponseTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'content',
        'type',
        'usage_count',
        'user_id',
    ];

    protected $casts = [
        'usage_count' => 'integer',
    ];

    /**
     * Get the categories that belong to this response template.
     */
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    /**
     * Increment the usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
    }

    /**
     * Get the user that owns this response template.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

