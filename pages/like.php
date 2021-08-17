<?php
include_once '../includes/header.php';
include_once '../includes/db.php';

if (!isset($_SESSION['userId'])) {
    header('location: ../index.php?m=Login to continue');
    exit();
}

$post_id = $_GET['post'];
$user_id = $_SESSION['userId'];

// Checking if it is a request to like or dislike
$query = ($_GET['action'] === 'like' ?
    "INSERT INTO likes(`user_id`, `post_id`) VALUES ('$user_id','$post_id');" :
    "DELETE FROM likes WHERE user_id = '$user_id' AND post_id = '$post_id';");
mysqli_query($conn, $query);
header('location: ' . $_SERVER['HTTP_REFERER']);
exit();
