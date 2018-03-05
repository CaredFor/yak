<?php

namespace Benwilkins\Yak\Contracts\Models;


use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface ConversationState
{
    public function user(): BelongsTo;

    public function conversation(): BelongsTo;

    public function scopeOfUser($query, $userId);

    public function scopeOfConversation($query, $conversationId);
}