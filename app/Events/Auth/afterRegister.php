<?php

namespace App\Events\Auth;

use App\Http\Traits\App;
use App\Http\Traits\Modules;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class afterRegister
{
    use Dispatchable, InteractsWithSockets, SerializesModels, App;

    public $data_user;
    public $verification;
    public $response;
    public $role;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($data_user, $verification, $response, $role)
    {
        $this->data_user = $data_user;
        $this->verification = $verification;
        $this->role = $role;
        $this->response = $response;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
