<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

/** Receives signed lifecycle hooks from MediaMTX, never from browsers. */
class MediaController extends ApiController
{
    public function streamStatus()
    {
        $this->request->allowMethod(['post']);
        $secret = (string)Configure::read('MediaMtx.webhookSecret', '');
        $signature = $this->request->getHeaderLine('X-Eaststream-Media-Signature');
        if ($secret === '' || !hash_equals($secret, $signature)) {
            return $this->json(['message' => 'Invalid media webhook signature.'], 401);
        }

        $path = (string)$this->request->getQuery('path', '');
        $state = (string)$this->request->getQuery('state', '');
        if ($path === '' || !in_array($state, ['online', 'offline'], true)) {
            return $this->json(['message' => 'A stream path and valid state are required.'], 422);
        }

        $streams = $this->fetchTable('Streams');
        $stream = $streams->find()->where(['stream_key' => $path])->contain(['Channels'])->first();
        // Ignore paths that Eaststream did not issue a stream key for.
        if (!$stream) return $this->json(['accepted' => true]);

        $now = FrozenTime::now();
        if ($state === 'online') {
            $stream->status = 'live';
            $stream->started_at ??= $now;
            $stream->ended_at = null;
            $stream->channel->is_live = true;
        } else {
            $stream->status = 'offline';
            $stream->ended_at ??= $now;
            $stream->channel->is_live = false;
            $this->fetchTable('StreamViewers')->deleteAll(['stream_id' => $stream->id]);
            $stream->viewer_count = 0;
        }

        $streams->saveOrFail($stream);
        $this->fetchTable('Channels')->saveOrFail($stream->channel);
        return $this->json(['accepted' => true, 'stream_id' => $stream->id, 'status' => $stream->status]);
    }
}
