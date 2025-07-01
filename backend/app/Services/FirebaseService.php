<?php

namespace App\Services;

use Kreait\Firebase\Factory;

class FirebaseService
{
    protected $firestore;

    public function __construct()
    {
        $credentialsPath = base_path('storage/firebase/firebase_credentials.json');

        if (!file_exists($credentialsPath)) {
            throw new \Exception("Firebase credentials file not found at: $credentialsPath");
        }

        $factory = (new Factory)->withServiceAccount($credentialsPath);

        $this->firestore = $factory->createFirestore()->database();
    }

    public function pushMessageToFirestore(array $message)
    {
        $chatId = $this->getChatId($message['sender_id'], $message['receiver_id']);

        $this->firestore
            ->collection('chats')
            ->document($chatId)
            ->collection('messages')
            ->add([
                'sender_id' => $message['sender_id'],
                'receiver_id' => $message['receiver_id'],
                'message' => $message['message'],
                'created_at' => now(),
            ]);
    }

    protected function getChatId($id1, $id2)
    {
        return $id1 < $id2 ? "{$id1}_{$id2}" : "{$id2}_{$id1}";
    }
}
