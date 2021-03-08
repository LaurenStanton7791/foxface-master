<?php
require_once '/var/www/html3/inc/bootstrap.php';

$user_passcode = request()->get('user_passcode');
$user_name     = request()->get('user_name');
$user_email    = request()->get('user_email');
$password      = request()->get('password');
$confirmPass   = request()->get('confirm_pass');

if (!checkValidEmail($user_email)) {
    $msg = "$user_email does not describe a valid email account";
    $session->getFlashBag()->add('error', $msg);
    redirect('/register.php');
}

if ($password != $confirmPass) {
    $session->getFlashBag()->add('success', 'Passwords do NOT match');
    redirect('/register.php');
}

$user = findUserByEmail($user_email);
if (!empty($user)) {
    $session->getFlashBag()->add('error', 'User Already Exists');
    redirect('/register.php');
}

if ($user_passcode) {
    $invite = findInviteByPasscode($user_passcode);
    if (!empty($hoh)) {
        $session->getFlashBag()->add('error', 'Passcode invite doesn\'t exist');
        redirect('/register.php');
    }
}

$hashed = password_hash($password, PASSWORD_DEFAULT);

$user_type = 5; 
if ($invite['hoh_id'] > 0) { 
    $user_type = 3; 
}

$user = createUser($invite['mi_id'], $user_hoh, $invite['pm_id'], $user_name, $user_email, $user_type, $hashed);

/* check and add if necessary a demographics form */
$member_forms = addMemberForms($user["user_id"],"HouseholdDemographics");

$expTime = time() + 3600;

$jwt = \Firebase\JWT\JWT::encode([
    'iss' => request()->getBaseUrl(),
    'sub' => "{$user['id']}",
    'exp' => $expTime,
    'iat' => time(),
    'nbf' => time(), 
    'is_type' => $user['user_type'] == 1,
    'hoh' => $user['user_hoh']
], getenv("SECRET_KEY"),'HS256');

$accessToken = new Symfony\Component\HttpFoundation\Cookie('access_token', $jwt, $expTime, '/', getenv('COOKIE_DOMAIN'));

$session->getFlashBag()->add('success', 'User Added... You can now log into system');
redirect('/index.php',['cookies' => [$accessToken]]);

?>