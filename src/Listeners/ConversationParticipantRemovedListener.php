<?php


namespace Benwilkins\Yak\Listeners;

use Benwilkins\Yak\Events\ConversationParticipantRemoved;
use Benwilkins\Yak\Enums\ListenerMessages;

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
        $removedMsg = str_replace('{$user}', $event->participant->name, ListenerMessages::USER_REMOVED_CHAT);
        $event->conversation->messages()->create([
            'author_id' => $event->participant->id,
            'body' => $removedMsg,
            'message_type' => 'system'
        ]);
    }
}


