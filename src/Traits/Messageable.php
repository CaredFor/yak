<?php


namespace Benwilkins\Yak\Traits;

use Benwilkins\Yak\Contracts\Models\Conversation;
use Benwilkins\Yak\Contracts\Models\ConversationState;
use Benwilkins\Yak\Contracts\Models\Message;
use Benwilkins\Yak\Facades\Yak;
use Illuminate\Support\Collection;

/**
 * Trait Messageable
 * @package Benwilkins\Yak\Traits
 */
trait Messageable
{
    /**
     * @return mixed
     */
    public function conversations()
    {
        return $this->belongsToMany(Yak::getConversationClass(), 'conversation_participants', 'user_id', 'conversation_id');
    }

    /**
     * @param int $readCount
     * @return Collection
     */
    public function conversationList($readCount = 8): Collection
    {
        $list = $this->unreadConversations()->sortByDesc('updated_at');
        $ids = $list->map(function ($item) {
            return $item->id;
        });

        return $list->concat($this->conversations()->whereNotIn('conversations.id', $ids->values()->toArray())->orderByDesc('updated_at')->take($readCount)->get());
    }

    /**
     * @return mixed
     */
    public function messages()
    {
        return $this->hasMany(Yak::getMessageClass(), 'author_id');
    }

    /**
     * @return int
     */
    public function unreadMessageCount()
    {
        $total = 0;

        /** @var ConversationState $state */
        foreach (Yak::getConversationStateClass()::ofUser($this->id)->where('read', false)->get() as $state) {
            $builder = Yak::getMessageClass()::ofConversation($state->conversation_id);

            if ($state->last_read_at) {
                $builder->where('created_at', '>', $state->last_read_at);
            }

            $total += $builder->count();
        }

        return $total;
    }

    /**
     * @return Collection
     */
    public function unreadConversations()
    {
        $states = Yak::getConversationStateClass()::ofUser($this->id)->where('read', false)->get();
        $conversations = new Collection();

        /** @var ConversationState $state */
        foreach ($states as $state) {
            $conversations->push($state->conversation);
        }

        return $conversations;
    }
}