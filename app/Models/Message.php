<?php

namespace App\Models;

class Message
{
    protected $id;
    public $senderId;
    public $receiverId;
    protected $message;

    public function __construct($id, $data)
    {
        $this->id = $id;
        $this->senderId = $data['sender_id'];
        $this->receiverId = $data['receiver_id'];
        $this->message = $data['message'];

    }

}
