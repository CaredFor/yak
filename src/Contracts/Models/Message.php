<?php

namespace Benwilkins\Yak\Contracts\Models;


use Illuminate\Database\Eloquent\Relations\BelongsTo;

interface Message
{
    public function author(): BelongsTo;

    public function conversation(): BelongsTo;

    public function scopeOfConversation($query, $conversationId);

    public function handleNewMessage();
}