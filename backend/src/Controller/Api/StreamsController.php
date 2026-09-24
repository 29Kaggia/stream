<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Cake\Core\Configure;
use Cake\I18n\FrozenTime;
use Cake\Utility\Text;

class StreamsController extends ApiController
{
    public function view(string $slug)
    {
        $this->request->allowMethod(['get']);
        $stream = $this->fetchTable('Streams')->find()
            ->where(['Channels.slug' => $slug, 'Streams.status' => 'live'])
            ->contain(['Channels' => ['Users' => ['Profiles'], 'Categories']])
            ->orderByDesc('Streams.started_at')
            ->first();
        if (!$stream) return $this->json(['message' => 'This stream is offline or unavailable.'], 404);

        $channel = $stream->channel;
        $following = false;
        $header = $this->request->getHeaderLine('Authorization');
        if ($header !== '' && preg_match('/^Bearer\s+(.+)$/i', $header)) {
            try {
                $user = $this->currentUser();
                $following = (bool)$this->fetchTable('Follows')->find()->where(['user_id' => $user->id, 'channel_id' => $channel->id])->first();
            } catch (\Throwable) {
                $following = false;
            }
        }
        return $this->json(['stream' => ['id' => $stream->id, 'title' => $stream->title, 'thumbnail_url' => $stream->thumbnail_url, 'playback_url' => $this->playbackUrl($stream->stream_key), 'viewer_count' => $stream->viewer_count, 'started_at' => $stream->started_at, 'channel' => ['id' => $channel->id, 'name' => $channel->name, 'slug' => $channel->slug, 'description' => $channel->description, 'category' => $channel->category?->name, 'username' => $channel->user->username, 'profile' => $channel->user->profile], 'following' => $following]]);
    }

    public function live()
    {
        $this->request->allowMethod(['get']);
        $streams = $this->fetchTable('Streams')->find()
            ->where(['Streams.status' => 'live'])
            ->contain(['Channels' => ['Users', 'Categories']])
            ->orderByDesc('Streams.started_at')->all();
        $data = [];
        foreach ($streams as $stream) {
            $channel = $stream->channel;
            $data[] = ['id' => $stream->id, 'title' => $stream->title, 'thumbnail_url' => $stream->thumbnail_url, 'viewer_count' => $stream->viewer_count, 'channel' => ['id' => $channel->id, 'name' => $channel->name, 'slug' => $channel->slug, 'category' => $channel->category?->name, 'username' => $channel->user->username]];
        }
        return $this->json(['streams' => $data]);
    }

    public function mine()
    {
        $this->request->allowMethod(['post', 'patch']);
        $user = $this->currentUser();
        if (!in_array($user->role, ['streamer', 'admin'], true)) return $this->json(['message' => 'Create a channel before creating a stream.'], 403);
        $channel = $this->fetchTable('Channels')->find()->where(['user_id' => $user->id])->first();
        if (!$channel) return $this->json(['message' => 'Create your channel first.'], 422);
        $streams = $this->fetchTable('Streams');
        $stream = $streams->find()->where([
            'channel_id' => $channel->id,
            'status IN' => ['prepared', 'live'],
        ])->orderByDesc('created')->first();
        if ($this->request->is('post') && $stream?->status === 'live') {
            return $this->json(['message' => 'End your current stream before preparing another broadcast.'], 409);
        }
        $stream ??= $streams->newEmptyEntity();
        // A creator preparing OBS or WHIP must never make a stream public. Only
        // MediaMTX's signed online webhook is allowed to set status to live.
        $status = $this->request->is('post') ? 'prepared' : (string)$this->request->getData('status', 'offline');
        if (!in_array($status, ['prepared', 'offline'], true)) return $this->json(['message' => 'A broadcast can only be prepared or ended here.'], 422);
        $isNew = $stream->isNew();
        $data = ['channel_id' => $channel->id, 'title' => trim((string)$this->request->getData('title', $stream->title ?? 'Untitled stream')), 'thumbnail_url' => trim((string)$this->request->getData('thumbnail_url', $stream->thumbnail_url ?? '')), 'status' => $status];
        if ($isNew) $data['stream_key'] = Text::uuid() . Text::uuid();
        if ($status === 'offline') { $data['ended_at'] = FrozenTime::now(); $channel->is_live = false; } else { $channel->is_live = false; }
        $stream = $streams->patchEntity($stream, $data);
        if (!$streams->save($stream)) return $this->json(['message' => 'Unable to save stream.', 'errors' => $stream->getErrors()], 422);
        $this->fetchTable('Channels')->saveOrFail($channel);
        return $this->json(['stream' => $stream, 'stream_key' => $stream->stream_key, 'ingest_url' => (string)Configure::read('MediaMtx.ingestUrl'), 'playback_url' => $this->playbackUrl($stream->stream_key), 'whip_url' => $this->whipUrl($stream->stream_key)], $isNew ? 201 : 200);
    }

    private function playbackUrl(string $streamKey): string
    {
        return rtrim((string)Configure::read('MediaMtx.playbackBaseUrl'), '/') . '/' . rawurlencode($streamKey) . '/index.m3u8';
    }

    private function whipUrl(string $streamKey): string
    {
        return rtrim((string)Configure::read('MediaMtx.webrtcBaseUrl'), '/') . '/' . rawurlencode($streamKey) . '/whip';
    }
}
