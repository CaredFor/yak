# Yak for Laravel

A robust messaging system built for the Laravel framework. Create conversations between many users or one-on-one.

Built for Laravel 5.4 and up.

## Installation

Start sending messages in just a few easy steps:

### 1. Add to Laravel via Composer

`composer require benwilkins/yak `

### 2. Register the Service Provider and Facade

In `config/app.php`, add the following:

```php
'providers' => [
    // ...
    Benwilkins\Yak\YakServiceProvider::class,
    // ...
],

'aliases' => [
    // ...
    'Yak' => Benwilkins\Yak\Facades\Yak::class,
    // ...
],
```

### 3. Publish the config

`php artisan vendor:publish`

### 4. Run the migrations

`php artisan migrate`

### 5. Add the trait to your User model

```php
<?php

namespace App;

use Benwilkins\Yak\Traits\Messageable;

class User extends Authenticatable
{
    use Notifiable, Messageable;
    // ...
```

## Usage

### Conversations

A __conversation__ can exist between two or more __participants__. Participants can be added and removed easily through some methods that are included on the Conversation model.

#### Starting or finding a conversation
Yak includes a method to easily find or create a conversation between participants:

```php
// One easy method
$conversation = Yak::startOrFindConversation([$userId1, $userId2, ...]);

// Find a conversation manually
$conversation = Yak::findConversation([$userId1, $userId2, ...]);
```

#### Adding/removing participants
You can easily add or remove participants from a conversation. If a conversation only has two participants, an exception will be thrown if you try to remove one of them.

```php
// Adding many participants
$conversation->addParticipants([$userId1, $userId2, ...]);

// Adding one participant
$conversation->addParticipants($userId);

// Removing many participants
$conversation->removeParticipants([$userId1, $userId2, ...]);

// Removing one participant
$conversation->removeParticipants($userId);
```

### Messages
#### Sending a message
Yak also includes a way to send a message to one or more users. This method will either find or create a conversation, and add the message to it automagically in one easy step. 

```php
$conversation = Yak::sendMessageToParticipants([$userId1, $userId2, ...]);
```

### Conversation States
A __conversation state__ will determine if the conversation has unread messages for a given user. The conversation model will include a `state_for_current_user` attribute.

### Participants (Users via Messageable trait)
By adding the `Messageable` trait to your User model, you can get all conversations and messages for a given user.

```php
// Get all conversations for the user
$conversations = $user->conversations;

// Get all messages sent by the user
$messages = $user->messages;

// Get a count of unread messages
$unreadCount = $user->unreadMessageCount();

// Get all conversations containing unread messages
$unreadConversations = $user->unreadConversations();
```

You can also get a "conversation list" for a user, which will return all unread conversations at the top of the list, followed by the next eight read conversations. You may override the number of read conversations to append to the the list.

```php
$conversations = $user->conversationList($readCount = 8);
``` 
 

### Events
#### ConversationStarted
When using the Yak facade to start a conversation, a `Benwilkins\Yak\Events\ConversationStarted` event will be fired.

#### MessageSent
When a new message is created, a `Benwilkins\Yak\Events\MessageSent` event will be fired.

#### YakConversationParticipantAdded
When adding a participant(s) to a conversation via the `addParticipants` method on the Conversation model, a `Benwilkins\Yak\Events\ConversationParticipantAdded` event will be fired.

#### YakConversationParticipantRemoved
When removing a participant(s) to a conversation via the `removeParticipants` method on the Conversation model, a `Benwilkins\Yak\Events\ConversationParticipantRemoved` event will be fired.

### Contracts
It is possible to extend all the models in the package, and even the Yak facade. To do so, simply create and register new service provider that binds the model contracts to the models you want to use.

_Note: I highly recommend just **extending** the current models. There are boot methods and model events contained in there that are important to the application._

#### Example

```php
<?php 

namespace App\Providers;

// App\MyConversation will extend Benwilkins\Yak\Models\Conversation
// and implement Benwilkins\Yak\Contracts\Models\Conversation
use App\MyConversation;  
use Benwilkins\Yak\Contracts\Models\Conversation as ConversationContract;
use Illuminate\Support\ServiceProvider;

class YakServiceProvider extends ServiceProvider {
	
	public function register()
	{
		$this->app->bind(
			ConversationContract::class,
			MyConversation::class
		);
	}
}
```  

And in `config/app.php`: 

```php
'providers' => [
	...
	Benwilkins\Yak\YakServiceProvider::class,
	App\Providers\YakServiceProvider::class, // Make sure this one comes after the one before it.
	...
],
```