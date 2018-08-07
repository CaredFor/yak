<?php


namespace Benwilkins\Yak;


use Benwilkins\Yak\Contracts\Models\Conversation;
use Benwilkins\Yak\Contracts\Models\ConversationState;
use Benwilkins\Yak\Contracts\Models\Message;
use Benwilkins\Yak\Contracts\Yakkable;
use Benwilkins\Yak\Events\ConversationStarted;
use Benwilkins\Yak\Exceptions\InvalidUsersException;
use Benwilkins\Yak\Enums\MessageTypes;
use Benwilkins\Yak\Models\YakBaseModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class Yak
 * @package Benwilkins\Yak
 */
class Yak implements Yakkable
{
    /** @var string */
    protected $conversationClass;
    /** @var string */
    protected $conversationStateClass;
    /** @var string */
    protected $messageClass;

    public function __construct(Conversation $conversation, ConversationState $conversationState, Message $message)
    {
        $this->conversationClass = get_class($conversation);
        $this->conversationStateClass = get_class($conversationState);
        $this->messageClass = get_class($message);
    }

    /**
     * {@inheritdoc}
     */
    public function startOrFindConversation(array $userIds): Conversation
    {
        if (!$conversation = $this->findConversation($userIds)) {
            return $this->startConversation($userIds);
        }

        return $conversation;
    }

    /**
     * {@inheritdoc}
     */
    public function findConversation(array $userIds)
    {
        if (count($userIds) < 2) {
            throw InvalidUsersException::minimumNotMet();
        }

        return $this->conversationClass::between($userIds)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function setConversationReadByUser(string $conversationId, $userId): ConversationState
    {
        $state = $this->conversationStateClass::updateOrCreate(
            ['conversation_id' => $conversationId, 'user_id' => $userId],
            ['read' => true, 'last_read_at' => new Carbon()]
        );

        return $state;
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessageToParticipants(string $body, $userIds, $authorId = null, $message_type = MessageTypes::DEFAULT): Conversation
    {
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        $author = $authorId ?: Auth::id();

        array_push($userIds, $author);

        $conversation = $this->startOrFindConversation($userIds);

        $conversation->messages()->create([
            'author_id' => $author,
            'body' => $body,
            'message_type' => $message_type
        ]);

        return $conversation->load(['messages']);
    }

    /**
     * {@inheritdoc}
     */
    public function getConversationClass(): string
    {
        return $this->conversationClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getConversationStateClass(): string
    {
        return $this->conversationStateClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageClass(): string
    {
        return $this->messageClass;
    }

    /**
     * Starts a new conversation between two or more users.
     * @param array $userIds
     * @return Conversation
     * @throws InvalidUsersException
     */
    protected function startConversation(array $userIds): Conversation
    {
        if (count($userIds) < 2) {
            throw InvalidUsersException::minimumNotMet();
        }

        /** @var Conversation $conversation */
        $conversation = $this->conversationClass::create();
        $userClass = YakBaseModel::userClass();

        foreach ($userIds as $userId) {
            $userClass::findOrFail($userId);
            $conversation->participants()->attach($userId);
        }

        $conversation->save();

        event(new ConversationStarted($conversation, DB::getDefaultConnection()));

        return $conversation;
    }
}