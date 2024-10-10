<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendMessage;

class ChatController extends Controller
{
    public function index($receiverId)
    {
        $messages = $this->messageFactory->getMessages(Auth::id(), $receiverId);

        return response()->json($messages);
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'receiver_id' => 'required|integer',
            'message' => 'required|string',
        ]);

        $data['sender_id'] = Auth::id();

        $message = $this->messageFactory->insertMessage($data);

        sendMessage::dispatch($message);

        return response()->json(['message_id' => $message->id], 201);
    }


}
