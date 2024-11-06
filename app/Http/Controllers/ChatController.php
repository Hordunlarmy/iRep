<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Jobs\SendMessage;
use App\Http\Resources\MessageResource;

class ChatController extends Controller
{
    public function index($id)
    {
        $messages = $this->messageFactory->getMessages(Auth::id(), $id);

        return response()->json(MessageResource::collection($messages));
    }

    public function getUnreadMessages()
    {
        $messages = $this->messageFactory->getUnreadMessages(Auth::id());

        return response()->json(MessageResource::collection($messages));
    }

    public function markAsRead($id)
    {
        $this->messageFactory->markAsRead($id);

        return response()->json(['message' => 'Message marked as read']);
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

        return response()->noContent();
    }

    public function delete($id)
    {
        if (!Auth::user()) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        $this->messageFactory->deleteMessage($id);

        return response()->noContent();
    }


}
