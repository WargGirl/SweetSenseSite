<?php

class ChatMessageFactory {

    public function createMessage(int $fromUserId, int $toUserId, string $message): array {
        return [
            'from_user_id' => $fromUserId,
            'to_user_id'   => $toUserId,
            'message'      => trim($message),
            'created_at'   => date('Y-m-d H:i:s')
        ];
    }
}