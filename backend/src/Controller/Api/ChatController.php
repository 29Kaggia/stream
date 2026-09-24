<?php
declare(strict_types=1);

namespace App\Controller\Api;

class ChatController extends ApiController
{
    public function messages(int $streamId)
    {
        $this->request->allowMethod(['get', 'post']);
        $messages = $this->fetchTable('ChatMessages');
        if ($this->request->is('post')) {
            $stream = $this->fetchTable('Streams')->find()->where(['id' => $streamId, 'status' => 'live'])->first();
            if (!$stream) return $this->json(['message' => 'This stream is no longer live.'], 409);
            $user = $this->currentUser();
            $message = trim((string)$this->request->getData('message'));
            if ($message === '') return $this->json(['message' => 'Message cannot be empty.'], 422);
            $chatMessage = $messages->newEntity(['stream_id' => $streamId, 'user_id' => $user->id, 'message' => mb_substr($message, 0, 500)]);
            if (!$messages->save($chatMessage)) return $this->json(['message' => 'Unable to send message.'], 422);
        }
        $items = $messages->find()->where(['stream_id' => $streamId])->contain(['Users'])->orderByDesc('ChatMessages.created')->limit(50)->all();
        $data = [];
        foreach ($items as $item) $data[] = ['id' => $item->id, 'message' => $item->message, 'created' => $item->created, 'user' => ['username' => $item->user->username]];
        return $this->json(['messages' => array_reverse($data)]);
    }
}
