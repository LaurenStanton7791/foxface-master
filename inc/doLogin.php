<?php
require '/var/www/html3/inc/bootstrap.php';

$user = findUserByEmail(request()->get('user_email'));

if (empty($user)) {
    $session->getFlashBag()->add('error', 'Username was not found');
    redirect('/index.php');
}

if (!password_verify(request()->get('password'), $user['password'])) {
    $session->getFlashBag()->add('error', 'Invalid Password');
    redirect('/index.php');
}

$expTime = time() + 18000;

$jwt = \Firebase\JWT\JWT::encode([
    'iss' => request()->getBaseUrl(),
    'sub' => $user['user_id'],
    'exp' => $expTime,
    'iat' => time(),
    'nbf' => time(), 
    'client' => $user["user_id"],
    'is_admin' => $user['role_id'] == 1
], getenv("SECRET_KEY"),'HS256');

$accessToken = new Symfony\Component\HttpFoundation\Cookie('access_token', $jwt, $expTime, '/', getenv('COOKIE_DOMAIN'));

$this_menu = request()->get('destination');

$session->getFlashBag()->add('success', "Successfully Logged In");

//Something to write to txt log
$log  = "User: ".$_SERVER['REMOTE_ADDR'].' - '.date("F j, Y, g:i a").PHP_EOL.
        "User: ".$user['email'].PHP_EOL.
        "-------------------------".PHP_EOL;
//Save string to log, use FILE_APPEND to append.
file_put_contents('/data/FoxFace/log_'.date("j.n.Y").'.log', $log, FILE_APPEND);

logUser($user['user_id'],$user['t_stamp']);
    
if ($user['role_id'] == 2) {
    redirect('/propertymanagerdashboard.php',['cookies' => [$accessToken]]);
} else {
    redirect('/dashboard.php',['cookies' => [$accessToken]]);
}

?>