<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Cake\I18n\FrozenTime;

/**
 * A short-lived SSE snapshot. EventSource reconnects after the supplied retry
 * interval, which works behind ordinary PHP/FastCGI deployments without holding
 * a worker open for every viewer.
 */
class EventsController extends ApiController
{
    public function stream(int $streamId)
    {
        $this->request->allowMethod(['get']);
        $stream = $this->fetchTable('Streams')->find()->where(['id' => $streamId])->first();
        if (!$stream) return $this->json(['message' => 'Stream not found.'], 404);

        $now = FrozenTime::now();
        $viewerCount = $this->fetchTable('StreamViewers')->find()
            ->where(['stream_id' => $streamId, 'expires_at >' => $now])->count();
        $messages = $this->fetchTable('ChatMessages')->find()->where(['stream_id' => $streamId])
            ->contain(['Users'])->orderByDesc('ChatMessages.created')->limit(50)->all();
        $chat = [];
        foreach ($messages as $message) {
            $chat[] = ['id' => $message->id, 'message' => $message->message, 'created' => $message->created, 'user' => ['username' => $message->user->username]];
        }
        $payload = ['stream_id' => $streamId, 'status' => $stream->status, 'viewer_count' => $viewerCount, 'messages' => array_reverse($chat)];
        $body = "retry: 3000\nevent: snapshot\ndata: " . json_encode($payload) . "\n\n";
        return $this->response->withType('text/event-stream')->withHeader('Cache-Control', 'no-cache, no-transform')
            ->withHeader('X-Accel-Buffering', 'no')->withStringBody($body);
    }
}
