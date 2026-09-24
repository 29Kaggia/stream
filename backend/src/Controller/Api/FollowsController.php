<?php
declare(strict_types=1);

namespace App\Controller\Api;

class FollowsController extends ApiController
{
    public function toggle(int $channelId)
    {
        $this->request->allowMethod(['post', 'delete']);
        $user = $this->currentUser();
        $channels = $this->fetchTable('Channels');
        if (!$channels->find()->where(['id' => $channelId])->first()) return $this->json(['message' => 'Channel not found.'], 404);
        if ($this->request->is('delete')) {
            $follow = $this->fetchTable('Follows')->find()->where(['user_id' => $user->id, 'channel_id' => $channelId])->first();
            if ($follow) $this->fetchTable('Follows')->delete($follow);
            return $this->json(['following' => false]);
        }
        if ($user->id === $channels->get($channelId)->user_id) return $this->json(['message' => 'You cannot follow your own channel.'], 422);
        $follows = $this->fetchTable('Follows');
        $existing = $follows->find()->where(['user_id' => $user->id, 'channel_id' => $channelId])->first();
        if (!$existing && !$follows->save($follows->newEntity(['user_id' => $user->id, 'channel_id' => $channelId]))) return $this->json(['message' => 'Unable to follow channel.'], 422);
        return $this->json(['following' => true], 201);
    }
}
