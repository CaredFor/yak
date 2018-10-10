<?php

namespace Benwilkins\Yak\Models;


use Benwilkins\Yak\Contracts\Helpers\Tenant;
use Benwilkins\Yak\Contracts\Models\Conversation as ConversationContract;
use Benwilkins\Yak\Enums\ReadStates;
use Benwilkins\Yak\Events\ConversationParticipantAdded;
use Benwilkins\Yak\Events\ConversationParticipantRemoved;
use Benwilkins\Yak\Exceptions\InvalidUsersException;
use Benwilkins\Yak\Facades\Yak;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class Conversation
 * @package Benwilkins\Yak\Models
 */
class Conversation extends YakBaseModel implements ConversationContract
{
    public $incrementing = false;

    /**
     * @var array
     */
    public $appends = ['state_for_current_user', 'last_message'];

    protected static function boot()
    {
        parent::boot();
//        self::bootUuidForKey();

        static::saving(function (Conversation $model) {
            /** @var Collection $users */
            $users = DB::table('conversation_participants')->select('user_id')->where('conversation_id', $model->id)->get();
            $index = $users->sortBy('user_id')->map(function ($row) { return $row->user_id; })->values()->all();

            $model->participants_index = json_encode($index);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(self::userClass(), 'conversation_participants', 'conversation_id', 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Yak::getMessageClass());
    }

    /**
     * @return mixed
     */
    public function lastMessage()
    {
        return Yak::getMessageClass()::ofConversation($this->id)->latest()->first();
    }

    /**
     * @return mixed
     */
    public function getLastMessageAttribute()
    {
        return $this->lastMessage();
    }

    /**
     * @return string
     */
    public function getStateForCurrentUserAttribute(): string
    {
        // Read if there are no messages in the conversation
        if (Yak::getMessageClass()::ofConversation($this->id)->count() === 0) {
            return ReadStates::READ;
        }

        // Unread if the users has never seen the conversation
        /** @var \Benwilkins\Yak\Contracts\Models\ConversationState $state */
        if (!$state = Yak::getConversationStateClass()::ofUser(Auth::id())->ofConversation($this->id)->latest()->first()) {
            return ReadStates::UNREAD;
        }

        return $state->read ? ReadStates::READ : ReadStates::UNREAD;
    }

    public function scopeBetween($query, $users)
    {
        if (is_array($users)) {
            $users = json_encode(collect($users)->sort()->values()->all());
        }

        return $query->where('participants_index', $users);
    }

    /**
     * @param array|int|string $userIds
     */
    public function addParticipants($userIds)
    {
        $this->participants()->attach($userIds);
        $this->touch();
        $this->refresh();

        if (! is_array($userIds)) {
            $userIds = [$userIds];
        }

        foreach ($userIds as $userId) {
            event(new ConversationParticipantAdded($this, YakBaseModel::userClass()::find($userId), resolve(Tenant::class)->current()));
        }
    }

    /**
     * @param array|int|string $userIds
     * @throws InvalidUsersException
     */
    public function removeParticipants($userIds)
    {
        if ($this->participants()->count() <= 2) {
            throw InvalidUsersException::minimumNotMet();
        }

        $this->participants()->detach($userIds);
        $this->touch();
        $this->refresh();

        if (! is_array($userIds)) {
            $userIds = [$userIds];
        }

        foreach ($userIds as $userId) {
            event(new ConversationParticipantRemoved($this, YakBaseModel::userClass()::find($userId), resolve(Tenant::class)->current()));
        }
    }
}