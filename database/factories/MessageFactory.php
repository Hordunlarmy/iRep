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
        $query = "
		SELECT s.id, s.message, sender.name AS sender,
             receiver.name AS receiver, s.sent_at, s.read_at, s.edited_at
		FROM messages s
		INNER JOIN accounts AS sender ON s.sender_id = sender.id
		INNER JOIN accounts AS receiver ON s.receiver_id = receiver.id
		WHERE (s.sender_id = ? AND s.receiver_id = ?)
		OR (s.sender_id = ? AND s.receiver_id = ?)
		ORDER BY s.sent_at ASC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$senderId, $receiverId, $receiverId, $senderId]);

        return $stmt->fetchAll();
    }

    public function getUnreadMessages($receiverId)
    {
        $query = "SELECT s.id, s.message, sender.name AS sender,
                     receiver.name AS receiver, s.sent_at, s.read_at, s.edited_at
              FROM messages s
              INNER JOIN accounts AS sender ON s.sender_id = sender.id
              INNER JOIN accounts AS receiver ON s.receiver_id = receiver.id
              WHERE s.receiver_id = ? AND s.read_at IS NULL";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$receiverId]);

        return $stmt->fetchAll();
    }

    public function markAsRead($id)
    {
        $query = "UPDATE messages SET read_at = NOW() WHERE id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
    }

    public function deleteMessage($id)
    {
        $query = "DELETE FROM messages WHERE id = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
    }
}
