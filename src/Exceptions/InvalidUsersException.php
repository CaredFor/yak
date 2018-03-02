<?php


namespace Benwilkins\Yak\Exceptions;


class InvalidUsersException extends \Exception
{
    public static function minimumNotMet()
    {
        return new static('A conversation must include at least two users.');
    }
}