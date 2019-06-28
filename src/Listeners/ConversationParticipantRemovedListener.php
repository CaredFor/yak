<?php


namespace Benwilkins\Yak\Listeners;

use Benwilkins\Yak\Events\ConversationParticipantRemoved;
use Benwilkins\Yak\Enums\ListenerMessages;
use Benwilkins\Yak\Enums\MessageTypes;
use Benwilkins\Yak\Models\ConversationState;

class ConversationParticipantRemovedListener {
    public $removedMsg;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ConversationParticipantRemoved $event
     * @return void
     */
    public function handle(ConversationParticipantRemoved $event)
    {
        $conversationStateForRemovedMember = ConversationState::where('user_id', $event->participant->id)->where('conversation_id', $event->conversation->id)->first();
        $conversationStateForRemovedMember->delete();

        $removedMsg = str_replace('{$user}', $event->participant->name, ListenerMessages::USER_REMOVED_CHAT);
        $event->conversation->messages()->create([
            'author_id' => $event->participant->id,
            'body' => $removedMsg,
            'message_type' => MessageTypes::SYSTEM
        ]);
    }
}


