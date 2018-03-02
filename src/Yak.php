<?php


namespace Benwilkins\Yak;


use App\User;
use Benwilkins\Yak\Contracts\Yakkable;
use Benwilkins\Yak\Events\ConversationStarted;
use Benwilkins\Yak\Exceptions\InvalidUsersException;
use Benwilkins\Yak\Models\Conversation;
use Benwilkins\Yak\Models\ConversationState;
use Benwilkins\Yak\Models\YakBaseModel;
use Benwilkins\Yak\Traits\Messageable;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Class Yak
 * @package Benwilkins\Yak
 */
class Yak implements Yakkable
{
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

        return Conversation::between($userIds)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function setConversationReadByUser(string $conversationId, $userId): ConversationState
    {
        $state = ConversationState::updateOrCreate(
            ['conversation_id' => $conversationId, 'user_id' => $userId],
            ['read' => true, 'last_read_at' => new Carbon()]
        );

        return $state;
    }

    /**
     * {@inheritdoc}
     */
    public function sendMessageToParticipants(string $body, $userIds, $authorId = null): Conversation
    {
        if (!is_array($userIds)) {
            $userIds = [$userIds];
        }

        $author = $authorId ?: Auth::id();

        array_push($userIds, $author);

        $conversation = $this->startOrFindConversation($userIds);

        $conversation->messages()->create([
            'author_id' => $author,
            'body' => $body
        ]);

        return $conversation->load(['messages']);
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
        $conversation = Conversation::create();
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