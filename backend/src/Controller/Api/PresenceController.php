<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Cake\I18n\FrozenTime;

class PresenceController extends ApiController
{
    public function heartbeat(int $streamId)
    {
        $this->request->allowMethod(['post']);
        $sessionId = (string)$this->request->getData('session_id', '');
        if (!preg_match('/^[a-f0-9-]{36}$/i', $sessionId)) {
            return $this->json(['message' => 'A valid viewer session is required.'], 422);
        }

        $stream = $this->fetchTable('Streams')->find()->where(['id' => $streamId, 'status' => 'live'])->first();
        if (!$stream) return $this->json(['message' => 'This stream is no longer live.'], 409);

        $viewers = $this->fetchTable('StreamViewers');
        $now = FrozenTime::now();
        $viewers->deleteAll(['expires_at <=' => $now]);
        $hash = hash('sha256', $sessionId);
        $viewer = $viewers->find()->where(['stream_id' => $streamId, 'session_hash' => $hash])->first();
        $viewer ??= $viewers->newEntity(['stream_id' => $streamId, 'session_hash' => $hash]);
        $viewer->expires_at = $now->addSeconds(45);
        $viewers->saveOrFail($viewer);

        $count = $viewers->find()->where(['stream_id' => $streamId, 'expires_at >' => $now])->count();
        if ($stream->viewer_count !== $count) {
            $stream->viewer_count = $count;
            $this->fetchTable('Streams')->saveOrFail($stream);
        }
        return $this->json(['viewer_count' => $count, 'expires_in_seconds' => 45]);
    }
}
