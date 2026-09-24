<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\Http\Exception\UnauthorizedException;
use Cake\Utility\Security;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiController extends AppController
{
    protected function json(array $data, int $status = 200)
    {
        return $this->response
            ->withType('application/json')
            ->withStatus($status)
            ->withStringBody(json_encode($data));
    }

    protected function currentUser()
    {
        $header = $this->request->getHeaderLine('Authorization');
        if (!preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            throw new UnauthorizedException('A bearer token is required.');
        }

        $configuredSecret = env('EASTSTREAM_JWT_SECRET');
        $secret = is_string($configuredSecret) && $configuredSecret !== ''
            ? $configuredSecret
            : hash_hmac('sha256', 'eaststream-jwt-signing-key', Security::getSalt());

        try {
            $payload = JWT::decode($matches[1], new Key($secret, 'HS256'));
        } catch (\Throwable) {
            throw new UnauthorizedException('Your session is invalid or has expired.');
        }

        $user = $this->fetchTable('Users')->find()
            ->where(['id' => (int)$payload->sub, 'status' => 'active'])
            ->first();
        if (!$user) {
            throw new UnauthorizedException('Your account is unavailable.');
        }

        return $user;
    }

    protected function requireAdmin()
    {
        $user = $this->currentUser();
        if ($user->role !== 'admin') {
            return null;
        }

        return $user;
    }
}
