<?php

namespace Benwilkins\Yak\Events;

use Benwilkins\Yak\Enums\BroadcastChannels;
use Benwilkins\Yak\Models\Message;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Facades\DB;

class MessageSent extends YakEvent
{
    use InteractsWithSockets;

    protected $message;

    /**
     * Create a new event instance.
     *
     * @param Message $message
     * @param string $connectionName
     */
    public function __construct(Message $message, string $connectionName = null)
    {
        $this->connectionName = $connectionName ?: DB::getDefaultConnection();
        $this->message = $message;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        $channels = [new Channel(BroadcastChannels::CONVERSATION_PREFIX . $this->message->conversation_id)];

        foreach ($this->message->conversation->participants as $participant) {
            if ($this->message->author_id !== $participant->id) {
                array_push($channels, new PrivateChannel(BroadcastChannels::PRIVATE_PREFIX . $participant->id));
            }
        }

        return $channels;
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return ['message' => $this->message->load('author')];
    }
}
