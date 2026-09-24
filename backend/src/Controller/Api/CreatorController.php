<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Cake\I18n\FrozenTime;

class CreatorController extends ApiController
{
    public function analytics()
    {
        $this->request->allowMethod(['get']);
        $user = $this->currentUser();
        if (!in_array($user->role, ['streamer', 'admin'], true)) {
            return $this->json(['message' => 'Streamer access is required.'], 403);
        }

        $channel = $this->fetchTable('Channels')->find()->where(['user_id' => $user->id])->contain(['Categories'])->first();
        if (!$channel) return $this->json(['message' => 'Create your channel first.'], 422);

        $streams = $this->fetchTable('Streams');
        $recent = $streams->find()->where(['channel_id' => $channel->id])->orderByDesc('created')->limit(10)->all();
        $allStreams = $streams->find()->where(['channel_id' => $channel->id])->all();
        $now = FrozenTime::now();
        $totalLiveSeconds = 0;
        foreach ($allStreams as $stream) {
            if (!$stream->started_at) continue;
            $end = $stream->ended_at ?? $now;
            $totalLiveSeconds += max(0, $end->getTimestamp() - $stream->started_at->getTimestamp());
        }
        $streamData = [];
        foreach ($recent as $stream) {
            $durationSeconds = 0;
            if ($stream->started_at) {
                $end = $stream->ended_at ?? $now;
                $durationSeconds = max(0, $end->getTimestamp() - $stream->started_at->getTimestamp());
            }
            $streamData[] = ['id' => $stream->id, 'title' => $stream->title, 'status' => $stream->status, 'viewer_count' => $stream->viewer_count, 'started_at' => $stream->started_at, 'ended_at' => $stream->ended_at, 'duration_seconds' => $durationSeconds];
        }
        $live = $streams->find()->where(['channel_id' => $channel->id, 'status' => 'live'])->first();
        $followers = $this->fetchTable('Follows')->find()->where(['channel_id' => $channel->id])->count();
        $broadcasts = $streams->find()->where(['channel_id' => $channel->id])->count();
        $peakViewers = $streams->find()->select(['peak' => 'MAX(viewer_count)'])->where(['channel_id' => $channel->id])->first();

        $liveDurationSeconds = 0;
        if ($live?->started_at) {
            $liveDurationSeconds = max(0, $now->getTimestamp() - $live->started_at->getTimestamp());
        }

        return $this->json(['channel' => ['id' => $channel->id, 'name' => $channel->name, 'slug' => $channel->slug, 'category' => $channel->category?->name, 'is_live' => $channel->is_live], 'metrics' => ['followers' => $followers, 'broadcasts' => $broadcasts, 'live_viewers' => $live?->viewer_count ?? 0, 'peak_viewers' => (int)($peakViewers?->get('peak') ?? 0), 'total_live_seconds' => $totalLiveSeconds, 'live_duration_seconds' => $liveDurationSeconds], 'recent_streams' => $streamData, 'note' => 'Live time includes completed broadcasts and the current broadcast. Viewer metrics update when a video provider reports them.']);
    }
}
