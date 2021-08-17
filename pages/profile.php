<?php
include_once '../includes/header.php';
include_once '../includes/db.php';

// Get profile user
$id = (isset($_GET['id']) ? $_GET['id'] : $_SESSION['userId']);

$query = "SELECT full_name, email, bio FROM user WHERE id = '$id' LIMIT 1";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) === 0) {
    header('location: ./home.php');
    exit;
}
$row = mysqli_fetch_array($result);
?>

<!-- User info -->
<table style="margin: auto; margin-bottom: 30px;">
    <tr>
        <td>
            <h2 style="margin: 0;"><?php echo $row['full_name'] ?></h2>
        </td>
        <td style="padding-left: 15px;">
            <h4 style="margin: 0;"><i><?php echo $row['email'] ?></i></h4>
        </td>
        <td style="padding-left: 15px;">
            <?php
            if (isset($_SESSION['userId'])) {
                // Check if user is followed
                $user = $_SESSION['userId'];
                if ($id !== $user) {
                    $qry = "SELECT * FROM follow WHERE follower_user = '$user' AND followed_user = '$id' LIMIT 1";
                    $res = mysqli_query($conn, $qry);
                    $text = ((mysqli_num_rows($res) === 0) ? "FOLLOW" : "UNFOLLOW");
                    echo '<button id="followBtn" data-id="' . $id . '" style="font-size: 18px; color: blue">' . $text . '</button>';
                }
            }
            ?>
        </td>
    </tr>
    <tr>
        <td colspan="3" style="text-align: center;"><?php echo $row['bio'] ?></td>
    </tr>
</table>

<script>
    const followBtn = document.getElementById("followBtn")
    followBtn?.addEventListener("click", () => {
        const data = {
            uid: followBtn.getAttribute("data-id"),
            action: followBtn.textContent
        }
        const req = new XMLHttpRequest();
        req.open("POST", "./follow.php");
        req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        req.responseType = "json";
        req.onreadystatechange = () =>
            req.readyState === 4 && (followBtn.textContent = req.response.action)
        req.send(JSON.stringify(data));
    })
</script>

<?php
// Fetch posts
$query = "SELECT post.id, content, full_name, created_at, COUNT(likes.post_id) as likes
        FROM post 
        JOIN user 
        ON user.id = post.user_id 
        LEFT JOIN likes ON likes.post_id = post.id
        WHERE post.user_id = $id
        GROUP BY post.id
        ORDER BY post.id DESC; ";
$result = mysqli_query($conn, $query);
?>

<!-- Render posts -->
<div class="posts">
    <?php if (mysqli_num_rows($result) === 0) : ?>
        <h3 style="text-align: center;">User hasn't posted anything</h3>
    <?php else : ?>
        <?php while ($row = mysqli_fetch_array($result)) : ?>
            <table>
                <thead>
                    <th style="text-align: left;"><?php echo $row['full_name'] ?></th>
                    <th><?php echo $row['created_at'] ?></th>
                </thead>
                <tr>
                    <td style="width: 420px;">
                        <p><?php echo $row['content'] ?></p>
                    </td>
                </tr>
                <tr>
                    <?php
                    if (isset($user)) {
                        // Check if post is liked by user
                        $qry = "SELECT * FROM likes WHERE post_id = '" . $row['id'] . "' AND user_id = '$user' LIMIT 1";
                        $res = mysqli_query($conn, $qry) ?>
                        <?php if (mysqli_num_rows($res) === 0) : ?>
                            <td><a style='font-size: 23px;' href='./like.php?post=<?php echo $row["id"] ?>&action=like'>LIKE <?php echo $row["likes"] ?></a></td>
                        <?php else : ?>
                            <td><a style='font-size: 23px; color: red' href='./like.php?post=<?php echo $row['id'] ?>&action=dislike'>LIKE <?php echo $row['likes'] ?></a></td>
                        <?php endif ?>
                        <?php if ($user === $id) : ?>
                            <td><a href="./delete.php?id=<?php echo $row['id'] ?>"><button style="font-size: 18px; color: red;">Delete</button></a></td>
                        <?php endif ?>
                    <?php } else
                        echo "<td><a style='font-size: 23px;' href='./like.php?post=" . $row['id'] . "&action=like'>LIKE " . $row['likes'] . "</a></td>" ?>
                </tr>
            </table>
        <?php endwhile; ?>
    <?php endif ?>
</div>

<?php
include_once '../includes/footer.php';
