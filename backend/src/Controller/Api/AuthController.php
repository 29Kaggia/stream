<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\I18n\FrozenTime;
use Cake\Utility\Security;
use Firebase\JWT\JWT;

class AuthController extends AppController
{
    private string $jwtSecret;

    public function initialize(): void
    {
        parent::initialize();

        $this->request->allowMethod(['post']);
        $configuredSecret = env('EASTSTREAM_JWT_SECRET');
        $this->jwtSecret = is_string($configuredSecret) && $configuredSecret !== ''
            ? $configuredSecret
            : hash_hmac('sha256', 'eaststream-jwt-signing-key', Security::getSalt());
    }

    public function register()
    {
        $data = $this->request->getData();

        $username = trim((string)($data['username'] ?? ''));
        $email = strtolower(trim((string)($data['email'] ?? '')));
        $password = (string)($data['password'] ?? '');

        if ($username === '' || $email === '' || $password === '') {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Username, email and password are required.',
            ], 422);
        }

        if (strlen($password) < 8) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Password must be at least 8 characters.',
            ], 422);
        }

        $users = $this->fetchTable('Users');

        if ($users->find()->where(['username' => $username])->first()) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Username already exists.',
            ], 409);
        }

        if ($users->find()->where(['email' => $email])->first()) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Email already exists.',
            ], 409);
        }

        $user = $users->newEntity([
            'username' => $username,
            'email' => $email,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'viewer',
            'status' => 'active',
            'created' => FrozenTime::now(),
            'modified' => FrozenTime::now(),
        ]);

        if (!$users->save($user)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Unable to create account.',
                'errors' => $user->getErrors(),
            ], 500);
        }

        $this->fetchTable('Profiles')->saveOrFail($this->fetchTable('Profiles')->newEntity([
            'user_id' => $user->id,
            'display_name' => $user->username,
        ]));

        return $this->jsonResponse([
            'success' => true,
            'message' => 'Account created successfully.',
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ], 201);
    }

    public function login()
    {
        $data = $this->request->getData();

        $email = strtolower(trim((string)($data['email'] ?? '')));
        $password = (string)($data['password'] ?? '');

        $users = $this->fetchTable('Users');

        $user = $users->find()
            ->where([
                'email' => $email,
                'status' => 'active',
            ])
            ->first();

        if (!$user || !password_verify($password, $user->password)) {
            return $this->jsonResponse([
                'success' => false,
                'message' => 'Invalid email or password.',
            ], 401);
        }

        $now = time();

        $payload = [
            'iss' => 'eaststream',
            'sub' => (int)$user->id,
            'username' => $user->username,
            'role' => $user->role,
            'iat' => $now,
            'exp' => $now + (60 * 60 * 24 * 7),
        ];

        $token = JWT::encode($payload, $this->jwtSecret, 'HS256');

        return $this->jsonResponse([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);
    }

    private function jsonResponse(array $data, int $status = 200)
    {
        return $this->response
            ->withType('application/json')
            ->withStatus($status)
            ->withStringBody(json_encode($data));
    }
}
