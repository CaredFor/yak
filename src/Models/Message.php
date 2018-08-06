<?php


namespace Benwilkins\Yak\Models;

use Benwilkins\Yak\Contracts\Models\Message as MessageContract;
use Benwilkins\Yak\Events\MessageSent;
use Benwilkins\Yak\Facades\Yak;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;


/**
 * Class Message
 * @package Benwilkins\Yak\Models
 */
class Message extends YakBaseModel implements MessageContract
{
    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'author_id',
        'body',
        'message_type'
    ];

    protected static function boot()
    {
        parent::boot();
        self::bootUuidForKey();

        static::created(function (Message $model) {
            $model->handleNewMessage();
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(self::userClass());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Yak::getConversationClass());
    }

    /**
     * @param $query
     * @param $conversationId
     * @return mixed
     */
    public function scopeOfConversation($query, $conversationId)
    {
        return $query->where('conversation_id', $conversationId);
    }

    /**
     * Tasks for when a new message is created.
     */
    public function handleNewMessage()
    {
        // Add any tasks to run when a new message is created.
        $this->updateConversationStateForRecipients();
        $this->conversation->touch();
        $this->sendEvents();
    }

    /**
     * Sets the conversation state for recipients
     */
    protected function updateConversationStateForRecipients()
    {
        foreach ($this->conversation->participants as $participant) {
            if ($participant->id !== $this->author_id) {
                Yak::getConversationStateClass()::updateOrCreate(
                    ['conversation_id' => $this->conversation_id, 'user_id' => $participant->id],
                    ['read' => false]
                );
            }
        }
    }

    /**
     * Sends any Laravel events that should be sent when a new message is created.
     */
    protected function sendEvents()
    {
        event(new MessageSent($this, DB::getDefaultConnection()));
    }
}