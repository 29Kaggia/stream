<?php
/**
 * Routes configuration.
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    /*
     * API routes
     */
    $routes->prefix('Api', function (RouteBuilder $builder): void {
        $builder->post('/auth/register', [
            'controller' => 'Auth',
            'action' => 'register',
        ]);

        $builder->post('/auth/login', [
            'controller' => 'Auth',
            'action' => 'login',
        ]);

        $builder->connect('/profiles/me', ['controller' => 'Profiles', 'action' => 'me'], ['_method' => ['GET', 'PATCH']]);
        $builder->get('/profiles/{username}', ['controller' => 'Profiles', 'action' => 'view'])
            ->setPass(['username']);
        $builder->patch('/users/{id}/role', ['controller' => 'Users', 'action' => 'role'])
            ->setPatterns(['id' => '\\d+'])->setPass(['id']);
        $builder->get('/users', ['controller' => 'Users', 'action' => 'index']);
        $builder->patch('/users/{id}/status', ['controller' => 'Users', 'action' => 'status'])
            ->setPatterns(['id' => '\\d+'])->setPass(['id']);
        $builder->get('/admin/overview', ['controller' => 'Admin', 'action' => 'overview']);
        $builder->get('/creator/analytics', ['controller' => 'Creator', 'action' => 'analytics']);
        $builder->connect('/channels/me', ['controller' => 'Channels', 'action' => 'mine'], ['_method' => ['GET', 'POST', 'PATCH']]);
        $builder->connect('/channels/{channelId}/follow', ['controller' => 'Follows', 'action' => 'toggle'], ['_method' => ['POST', 'DELETE']])
            ->setPatterns(['channelId' => '\\d+'])->setPass(['channelId']);
        $builder->get('/categories', ['controller' => 'Categories', 'action' => 'index']);
        $builder->get('/discover', ['controller' => 'Discovery', 'action' => 'index']);
        $builder->post('/categories', ['controller' => 'Categories', 'action' => 'manage']);
        $builder->get('/reports', ['controller' => 'Reports', 'action' => 'index']);
        $builder->patch('/reports/{id}', ['controller' => 'Reports', 'action' => 'resolve'])
            ->setPatterns(['id' => '\\d+'])->setPass(['id']);
        $builder->get('/streams/live', ['controller' => 'Streams', 'action' => 'live']);
        $builder->get('/streams/{slug}', ['controller' => 'Streams', 'action' => 'view'])->setPass(['slug']);
        $builder->connect('/streams/me', ['controller' => 'Streams', 'action' => 'mine'], ['_method' => ['POST', 'PATCH']]);
        $builder->connect('/streams/{streamId}/chat', ['controller' => 'Chat', 'action' => 'messages'], ['_method' => ['GET', 'POST']])
            ->setPatterns(['streamId' => '\\d+'])->setPass(['streamId']);
        $builder->post('/streams/{streamId}/presence', ['controller' => 'Presence', 'action' => 'heartbeat'])
            ->setPatterns(['streamId' => '\\d+'])->setPass(['streamId']);
        $builder->get('/streams/{streamId}/events', ['controller' => 'Events', 'action' => 'stream'])
            ->setPatterns(['streamId' => '\\d+'])->setPass(['streamId']);
        $builder->post('/media/stream-status', ['controller' => 'Media', 'action' => 'streamStatus']);
    });

    /*
     * Web routes
     */
    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->connect('/', [
            'controller' => 'Pages',
            'action' => 'display',
            'home',
        ]);

        $builder->connect('/pages/*', 'Pages::display');

        $builder->fallbacks();
    });
};
