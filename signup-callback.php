<?php
require_once 'vendor/autoload.php';
require_once 'includes/config.php';

$client = new Google_Client();
$client->setClientId('23116784781-akp48q5123hjhmjo2444b7ctqqkgnpnk.apps.googleusercontent.com');
$client->setClientSecret('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');
$client->setRedirectUri('http://localhost/Tourism-Management-System/signup-callback.php');
$client->addScope('email');
$client->addScope('profile');

if (isset($_GET['code'])) {
    $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

    
    if (isset($token['error'])) {
        echo 'Error fetching the access token: ' . $token['error_description'];
        exit;
    }

    $client->setAccessToken($token['access_token']);

    
    $google_oauth = new Google_Service_Oauth2($client);
    $google_account_info = $google_oauth->userinfo->get();
    $email = $google_account_info->email;
    $name = $google_account_info->name;

   
    $sql = "SELECT * FROM tblusers WHERE EmailId = :email";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // User already exists, log them in
        $_SESSION['user_id'] = $user['id'];
        header('Location: index.php');
    } else {
        // User does not exist, register them
        $sql = "INSERT INTO tblusers (FullName, EmailId) VALUES (:name, :email)";
        $stmt = $dbh->prepare($sql);
        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        $_SESSION['user_id'] = $dbh->lastInsertId();
        header('Location: thankyou.php');
    }
} else {
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
}
?>
