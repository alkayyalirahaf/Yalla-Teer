<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
 
class AiConversation extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'title',
        'file_path',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function recommendations(): HasMany
    {
        return $this->hasMany(Recommendation::class, 'ai_conversation_id');
    }
}
