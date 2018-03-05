<?php


namespace Benwilkins\Yak\Contracts;


use Benwilkins\Yak\Exceptions\InvalidUsersException;
use Benwilkins\Yak\Contracts\Models\Conversation;
use Benwilkins\Yak\Contracts\Models\ConversationState;

interface Yakkable
{
    /**
     * Instantiates a conversation between two or more users.
     * @param array $userIds
     * @return Conversation
     */
    public function startOrFindConversation(array $userIds): Conversation;

    /**
     * Finds a conversation between two or more users.
     * @param array $userIds
     * @return Conversation|null
     * @throws InvalidUsersException
     */
    public function findConversation(array $userIds);

    /**
     * Updates the state of the conversation for a given user.
     * @param string $conversationId
     * @param $userId
     * @return ConversationState
     */
    public function setConversationReadByUser(string $conversationId, $userId): ConversationState;

    /**
     * Sends a new message to one or more users. This will automagically start or find a
     * conversation, and attach the message to the conversation.
     * @param string $body
     * @param array|int|string $userIds
     * @param null $authorId
     * @return Conversation
     */
    public function sendMessageToParticipants(string $body, $userIds, $authorId = null): Conversation;
}