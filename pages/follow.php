<?php
include_once '../includes/db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] != "POST" || !isset($_SESSION['userId']))
    exit;

$data = json_decode(file_get_contents('php://input'), true);
$follower_user = $_SESSION['userId'];
$followed_user = $data['uid'];

// Checking if it is a request to follow or unfollow
$query = ($data['action'] === 'FOLLOW' ?
    "INSERT INTO follow(`follower_user`, `followed_user`) VALUES ('$follower_user','$followed_user');" :
    "DELETE FROM follow WHERE follower_user = '$follower_user' AND followed_user = '$followed_user';");

mysqli_query($conn, $query);
echo json_encode(array("action" => ($data['action'] === 'FOLLOW' ? 'UNFOLLOW' : 'FOLLOW')));
exit;
