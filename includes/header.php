<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/social-without-media/styles.css">
    <title>Social Without Media</title>
</head>

<body style="width: 50%;margin: auto;">
    <nav>
        <a style="font-size: 25px; margin-right: 20px; color: darkolivegreen;" href="/social-without-media">SWM</a>
        <a style="font-size: 21px; margin-right: 20px;" href="/social-without-media/pages/search.php">Search</a>
        <?php if (isset($_SESSION['userId'])) : ?>
            <a style="font-size: 21px; margin-right: 20px;" href="/social-without-media/pages/profile.php">Profile</a>
            <a style="font-size: 21px; margin-right: 20px;" href="/social-without-media/pages/logout.php">Logout</a>
        <?php else : ?>
            <a style="font-size: 21px; margin-right: 20px;" href="/social-without-media/pages/register.php">Register</a>
        <?php endif ?>
    </nav>
    <h1 style="text-align: center; color: darkolivegreen;">Social Without Media</h1>