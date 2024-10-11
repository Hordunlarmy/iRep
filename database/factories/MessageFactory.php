<?php

namespace Database\Factories;

use Illuminate\Support\Facades\DB;
use App\Models\Message;

class MessageFactory
{
    protected $db;

    public function __construct($db = null)
    {
        $this->db = $db ?: DB::connection()->getPdo();
    }

    public function insertMessage($data)
    {
        $query = "
		INSERT INTO messages
		(sender_id, receiver_id, message)
		VALUES (?, ?, ?)";

        $stmt = $this->db->prepare($query);
        $stmt->execute([
            $data['sender_id'],
            $data['receiver_id'],
            $data['message'],
        ]);

        return new Message($this->db->lastInsertId(), $data);
    }

    public function getMessages($receiverId, $senderId)
    {
        $query = "SELECT * FROM messages
        WHERE (sender_id = ? AND receiver_id = ?)
        OR (sender_id = ? AND receiver_id = ?)
        ORDER BY created_at ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$senderId, $receiverId, $receiverId, $senderId]);

        return $stmt->fetchAll();
    }
}
