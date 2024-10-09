<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;
use App\Services\MediaHandler;

class Post
{
    protected $db;
    protected $title;
    protected $context;
    protected $media;
    protected $post_type;
    protected $creatorId;

    public function __construct($db = null, $data = [])
    {
        $this->db = $db ?: DB::connection()->getPdo();

        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        if (!empty($data['media'])) {
            $this->media = (new MediaHandler())->handleMediaFiles($data['media']);
        }

        if (is_array($this->media)) {
            $this->media = json_encode($this->media);
        }
    }

    public function insertPost()
    {
        $query = "
		INSERT INTO posts (title, context, media, post_type, creator_id)
		VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);

        try {
            $stmt->execute([
                $this->title,
                $this->context,
                $this->media,
                $this->post_type,
                $this->creatorId
            ]);

            return $this->db->lastInsertId();

        } catch (\PDOException $e) {
            if (str_contains($e->getMessage(), 'Duplicate entry')) {
                throw new \Exception('A post with this title already exists.', 409);
            }
        }
    }

}
