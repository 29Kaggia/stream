<?php
declare(strict_types=1);

namespace App\Controller\Api;

class DiscoveryController extends ApiController
{
    public function index()
    {
        $this->request->allowMethod(['get']);
        $query = trim((string)$this->request->getQuery('q', ''));
        $categorySlug = trim((string)$this->request->getQuery('category', ''));
        $followingOnly = (string)$this->request->getQuery('following', '') === '1';
        $viewerId = null;
        if ($this->request->getHeaderLine('Authorization') !== '') {
            try { $viewerId = $this->currentUser()->id; } catch (\Throwable) { $viewerId = null; }
        }

        $channels = $this->fetchTable('Channels')->find()
            ->contain(['Users' => ['Profiles'], 'Categories', 'Follows'])
            ->orderByDesc('Channels.is_live')->orderByDesc('Channels.modified')->limit(60)->all()->toList();
        $streams = $this->fetchTable('Streams')->find()->where(['status' => 'live'])
            ->contain(['Channels' => ['Users' => ['Profiles'], 'Categories', 'Follows']])->orderByDesc('started_at')->all()->toList();

        $matchesChannel = function ($channel) use ($query, $categorySlug, $viewerId, $followingOnly): bool {
            if ($categorySlug !== '' && $channel->category?->slug !== $categorySlug) return false;
            if ($followingOnly && (!$viewerId || !array_filter($channel->follows ?? [], fn($follow) => $follow->user_id === $viewerId))) return false;
            if ($query === '') return true;
            $needle = mb_strtolower($query);
            return str_contains(mb_strtolower($channel->name), $needle)
                || str_contains(mb_strtolower($channel->user->username), $needle)
                || str_contains(mb_strtolower($channel->category?->name ?? ''), $needle);
        };
        $matchesStream = function ($stream) use ($matchesChannel, $query, $categorySlug, $viewerId, $followingOnly): bool {
            if ($matchesChannel($stream->channel)) return true;
            if ($query === '' || ($categorySlug !== '' && $stream->channel->category?->slug !== $categorySlug)) return false;
            if ($followingOnly && (!$viewerId || !array_filter($stream->channel->follows ?? [], fn($follow) => $follow->user_id === $viewerId))) return false;
            return str_contains(mb_strtolower($stream->title), mb_strtolower($query));
        };
        $channels = array_values(array_filter($channels, $matchesChannel));
        $live = array_values(array_filter($streams, $matchesStream));
        $recent = $this->fetchTable('Streams')->find()->where(['status' => 'offline', 'ended_at IS NOT' => null])
            ->contain(['Channels' => ['Users' => ['Profiles'], 'Categories', 'Follows']])->orderByDesc('ended_at')->limit(30)->all()->toList();
        $recent = array_values(array_filter($recent, $matchesStream));

        $categories = $this->fetchTable('Categories')->find()
            ->select(['id', 'name', 'slug', 'description'])
            ->orderByAsc('name')->all()->toList();
        if ($query !== '') {
            $needle = mb_strtolower($query);
            $categories = array_values(array_filter($categories, fn($category) =>
                str_contains(mb_strtolower($category->name), $needle)
                || str_contains(mb_strtolower($category->description ?? ''), $needle)
            ));
        }

        return $this->json([
            'live' => array_map([$this, 'streamData'], $live),
            'recent' => array_map([$this, 'streamData'], array_slice($recent, 0, 12)),
            'recommended' => array_map([$this, 'channelData'], array_slice($channels, 0, 12)),
            'categories' => array_map(fn($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
            ], array_slice($categories, 0, 18)),
        ]);
    }

    private function streamData($stream): array
    {
        return ['id' => $stream->id, 'title' => $stream->title, 'viewer_count' => $stream->viewer_count, 'ended_at' => $stream->ended_at, 'channel' => $this->channelData($stream->channel)];
    }
    private function channelData($channel): array
    {
        return ['id' => $channel->id, 'name' => $channel->name, 'slug' => $channel->slug, 'username' => $channel->user->username, 'category' => $channel->category?->name, 'category_slug' => $channel->category?->slug, 'is_live' => (bool)$channel->is_live, 'followers' => count($channel->follows ?? []), 'profile' => ['display_name' => $channel->user->profile?->display_name, 'avatar_url' => $channel->user->profile?->avatar_url]];
    }
}
