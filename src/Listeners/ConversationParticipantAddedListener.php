<?php


namespace Benwilkins\Yak\Listeners;

use Benwilkins\Yak\Events\ConversationParticipantAdded;
use Benwilkins\Yak\Enums\ListenerMessages;

class ConversationParticipantAddedListener {
    public $addedMsg;
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
     * @param  ConversationParticipantAdded $event
     * @return void
     */
    public function handle(ConversationParticipantAdded $event)
    {
        $addedMsg = str_replace('{$user}', $event->participant->name, ListenerMessages::USER_ADDED_CHAT);
        $event->conversation->messages()->create([
            'author_id' => $event->participant->id,
            'body' => $addedMsg,
            'message_type' => 'system'
        ]);
    }
}


