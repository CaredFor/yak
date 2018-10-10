<?php

namespace Benwilkins\Yak;

use Benwilkins\Yak\Contracts\Helpers\Tenant as TenantContract;
use Benwilkins\Yak\Contracts\Models\Conversation as ConversationContract;
use Benwilkins\Yak\Contracts\Models\ConversationState as ConversationStateContract;
use Benwilkins\Yak\Contracts\Models\Message as MessageContract;
use Benwilkins\Yak\Helpers\Tenant;
use Benwilkins\Yak\Models\Conversation;
use Benwilkins\Yak\Models\ConversationState;
use Benwilkins\Yak\Models\Message;
use Illuminate\Support\ServiceProvider;

class YakServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/yak.php' => config_path('yak.php'),
        ]);

        $this->mergeConfigFrom(__DIR__.'/../config/yak.php', 'yak');
        $this->loadMigrationsFrom(__DIR__.'/../migrations');
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerFacades();
        $this->registerContracts();
    }

    protected function registerFacades()
    {
        $this->app->alias(Yak::class, 'yak');
    }

    protected function registerContracts()
    {
        $this->app->bind(TenantContract::class, Tenant::class);
        $this->app->bind(ConversationContract::class, Conversation::class);
        $this->app->bind(ConversationStateContract::class, ConversationState::class);
        $this->app->bind(MessageContract::class, Message::class);
    }
}
