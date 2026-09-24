<?php
declare(strict_types=1);

namespace App\Controller\Api;

use Cake\Utility\Text;

class ChannelsController extends ApiController
{
    public function mine()
    {
        $this->request->allowMethod(['get', 'post', 'patch']);
        $user = $this->currentUser();
        $channels = $this->fetchTable('Channels');
        $channel = $channels->find()->where(['user_id' => $user->id])->contain(['Categories'])->first();
        if ($this->request->is('get')) return $this->json(['channel' => $channel]);

        if ($user->role === 'viewer') {
            $user->role = 'streamer';
            $this->fetchTable('Users')->saveOrFail($user);
        }
        $name = trim((string)$this->request->getData('name', $channel?->name ?? $user->username));
        $categoryId = $this->request->getData('category_id');
        $data = [
            'user_id' => $user->id,
            'name' => $name,
            'slug' => $channel?->slug ?? Text::slug($name) . '-' . $user->id,
            'description' => trim((string)$this->request->getData('description', $channel?->description ?? '')),
            'category_id' => $categoryId === null || $categoryId === '' ? null : (int)$categoryId,
        ];
        $channel ??= $channels->newEmptyEntity();
        $channel = $channels->patchEntity($channel, $data);
        if (!$channels->save($channel)) return $this->json(['message' => 'Unable to save channel.', 'errors' => $channel->getErrors()], 422);
        return $this->json(['channel' => $channel], $this->request->is('post') ? 201 : 200);
    }
}
