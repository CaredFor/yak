<?php

namespace Benwilkins\Yak\Contracts\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface Conversation
{
    public function participants(): BelongsToMany;

    public function messages(): HasMany;

    public function lastMessage();

    public function getStateForCurrentUserAttribute(): string;

    public function scopeBetween($query, $users);

    public function addParticipants($userIds);

    public function removeParticipants($userIds);
}