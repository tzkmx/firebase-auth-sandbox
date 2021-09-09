<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// TODO: check for caches of public keys (ID token verification)
// and own authentication tokens (psr-cache implementation)
// https://firebase-php.readthedocs.io/en/5.x/setup.html

use Kreait\Firebase\Factory;
use Firebase\Auth\Token\Exception\InvalidToken;
use Kreait\Firebase\Exception\Auth\RevokedIdToken;

$factory = (new Factory)->withServiceAccount('secrets/samaya-260a0-00f3077666f9.json');

$auth = $factory->createAuth();

try {
    $idToken = $_POST['idToken'] ?? 'not';

    if($idToken === 'not') {
        throw new \InvalidArgumentException('idToken not present in request');
    }

    $verifiedIdToken = $auth->verifyIdToken($idToken, true);
} catch (InvalidToken $e) {
    handleTokenInvalidException($e);
} catch (RevokedIdToken $e) {
    handleTokenInvalidException($e);
} catch (\InvalidArgumentException $e) {
    http_send_status(400);
    echo 'The token could not be parsed: '.$e->getMessage() . "\n";
    exit();
}

$claims = $verifiedIdToken->claims();
$uid = $claims->get('sub');

/** @var \Kreait\Firebase\Auth\UserRecord $user */
$user = $auth->getUser($uid);

if ($user->emailVerified === false) {
    $auth->sendEmailVerificationLink($user->email);
}

error_log(var_export($user, true), 0);

echo json_encode($claims->all());

function handleTokenInvalidException(Exception $e) {
    http_send_status(401);
    header('Content-Type: application/json');
    echo '{"message":"unauthenticated","exception":"' .
        $e->getMessage()
        . '"}', "\n";
    exit();
}
