<?php


namespace Benwilkins\Yak\Enums;


class BroadcastChannels
{
    use Enumerable;

    const CONVERSATION_PREFIX = 'Yak.Conversation.';
    const PRIVATE_PREFIX = 'Yak.Private.User.';
}