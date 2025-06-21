<?php

namespace App\Services;
use App\Models\Message;
use Illuminate\Support\Facades\Log;

class MessageService
{
    public function sendMessage($to, $message)
    {
        // Logic to send a message
    }

    public function receiveMessages($from)
    {
        // Logic to receive messages
    }

    private function fetchMessages(bool $onlyTrashed, string $querySearch, string $sortOption, int $perPage): array
    {
        try{
            $query = $onlyTrashed ? Message::onlyTrashed() : Message::query();
            if ($querySearch !== '') {
                $query->where('content', 'LIKE', '%' . $querySearch . '%');
            }
            if ($sortOption === 'most_recent') {
                $query->orderBy('created_at', 'desc');
            } else {
                $query->orderBy('created_at', 'asc');
            }
            $messages = $query->paginate($perPage);
            return ['data' => $messages];
        } catch (\Throwable $e) {
            return [
                Log::error('Đã xảy ra lỗi khi lấy danh sách tin nhắn: ' . $e->getMessage()),
                ['error' => 'Đã xảy ra lỗi khi lấy danh sách tin nhắn', 'status' => 500]
            ];
        }
    }

    public function getAvailableMessages(string $querySearch, string $sortOption, int $perPage): array
    {
        return $this->fetchMessages(false, $querySearch, $sortOption, $perPage);
    }
}
