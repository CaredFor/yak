<?php

namespace Benwilkins\Yak\Events;

use Benwilkins\Yak\Contracts\Models\Conversation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\DB;

class ConversationStarted extends YakEvent
{
    use InteractsWithSockets;

    public $conversation;

    /**
     * Create a new event instance.
     *
     * @param Conversation $conversation
     */
    public function __construct(Conversation $conversation, string $connectionName = null)
    {
        $this->connectionName = $connectionName ?: DB::getDefaultConnection();
        $this->conversation = $conversation;
    }

//    /**
//     * Get the channels the event should broadcast on.
//     *
//     * @return \Illuminate\Broadcasting\Channel|array
//     */
//    public function broadcastOn()
//    {
//        return new PrivateChannel('channel-name');
//    }
}
