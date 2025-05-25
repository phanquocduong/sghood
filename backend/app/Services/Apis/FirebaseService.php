<?php
namespace App\Services\Apis;

use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\FirebaseException;

class FirebaseService
{
    protected $firebaseAuth;

    public function __construct(FirebaseAuth $firebaseAuth)
    {
        $this->firebaseAuth = $firebaseAuth;
    }

    public function verifyToken(string $idToken)
    {
        if (empty($idToken)) {
            throw new \InvalidArgumentException('ID Token không được để trống');
        }

        try {
            $verifiedToken = $this->firebaseAuth->verifyIdToken($idToken);
            return $verifiedToken->claims()->get('phone_number');
        } catch (\Throwable $e) {
            Log::error($e->getMessage());
            throw new \Exception('Xác thực thất bại!');
        }
    }
}
