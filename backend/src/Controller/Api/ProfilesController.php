<?php
declare(strict_types=1);

namespace App\Controller\Api;

class ProfilesController extends ApiController
{
    public function me()
    {
        $this->request->allowMethod(['get', 'patch']);
        $user = $this->currentUser();
        $profiles = $this->fetchTable('Profiles');
        $profile = $profiles->find()->where(['user_id' => $user->id])->first();

        if ($this->request->is('patch')) {
            $profile ??= $profiles->newEmptyEntity();
            $profile = $profiles->patchEntity($profile, [
                'user_id' => $user->id,
                'display_name' => trim((string)$this->request->getData('display_name', $user->username)),
                'bio' => trim((string)$this->request->getData('bio', '')),
                'avatar_url' => trim((string)$this->request->getData('avatar_url', '')),
                'banner_url' => trim((string)$this->request->getData('banner_url', '')),
            ]);
            if (!$profiles->save($profile)) {
                return $this->json(['message' => 'Unable to save profile.', 'errors' => $profile->getErrors()], 422);
            }
        }

        return $this->json(['user' => ['id' => $user->id, 'username' => $user->username, 'email' => $user->email, 'role' => $user->role], 'profile' => $profile]);
    }

    public function view(string $username)
    {
        $this->request->allowMethod(['get']);
        $user = $this->fetchTable('Users')->find()->where(['username' => $username, 'status' => 'active'])->first();
        if (!$user) {
            return $this->json(['message' => 'Profile not found.'], 404);
        }
        $profile = $this->fetchTable('Profiles')->find()->where(['user_id' => $user->id])->first();
        $channels = $this->fetchTable('Channels');
        $channel = $channels->find()->where(['user_id' => $user->id])->contain(['Categories'])->first();
        if (!$channel) return $this->json(['user' => ['id' => $user->id, 'username' => $user->username, 'role' => $user->role], 'profile' => $profile, 'channel' => null, 'recent_streams' => []]);

        $streams = $this->fetchTable('Streams');
        $live = $streams->find()->where(['channel_id' => $channel->id, 'status' => 'live'])->orderByDesc('started_at')->first();
        $recent = $streams->find()->where(['channel_id' => $channel->id])->orderByDesc('created')->limit(6)->all();
        $following = false;
        $header = $this->request->getHeaderLine('Authorization');
        if ($header !== '' && preg_match('/^Bearer\s+(.+)$/i', $header)) {
            try {
                $viewer = $this->currentUser();
                $following = (bool)$this->fetchTable('Follows')->find()->where(['user_id' => $viewer->id, 'channel_id' => $channel->id])->first();
            } catch (\Throwable) {
                $following = false;
            }
        }

        $recentData = [];
        foreach ($recent as $stream) {
            $recentData[] = ['id' => $stream->id, 'title' => $stream->title, 'status' => $stream->status, 'viewer_count' => $stream->viewer_count, 'started_at' => $stream->started_at];
        }
        return $this->json(['user' => ['id' => $user->id, 'username' => $user->username, 'role' => $user->role], 'profile' => $profile, 'channel' => ['id' => $channel->id, 'name' => $channel->name, 'slug' => $channel->slug, 'description' => $channel->description, 'category' => $channel->category?->name, 'is_live' => $live !== null, 'followers' => $this->fetchTable('Follows')->find()->where(['channel_id' => $channel->id])->count(), 'following' => $following, 'live_stream' => $live ? ['title' => $live->title, 'viewer_count' => $live->viewer_count] : null], 'recent_streams' => $recentData]);
    }
}
