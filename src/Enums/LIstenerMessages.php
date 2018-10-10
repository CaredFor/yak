<?php


namespace Benwilkins\Yak\Enums;


class ListenerMessages
{
    use Enumerable;

    const USER_REMOVED_CHAT = '{$user} has left the chat.';
    const USER_ADDED_CHAT = '{$user} has joined the chat.';
}