<?php
include_once '../includes/db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    // Get content and insert into db
    $data = json_decode(file_get_contents('php://input'), true);
    $content = $data['content'];
    $uid = $_SESSION['userId'];
    $query = "INSERT INTO `post`(`user_id`, `content`) VALUES ('$uid', '$content')";
    $result = mysqli_query($conn, $query);
    // Return with response to append in dom
    $pid =  mysqli_insert_id($conn);
    $query = "SELECT * FROM post WHERE id = $pid LIMIT 1";
    $result = mysqli_query($conn, $query);
    $post = mysqli_fetch_array($result);
    echo json_encode(array("success" => true, "content" => $content, "id" => $post['id'], "created_at" => $post['created_at']));
    exit;
}
include_once '../includes/header.php';

if (!isset($_SESSION['userId'])) {
    header('location: ../index.php');
    exit();
}
$id = $_SESSION['userId'];
$query = "SELECT full_name, bio FROM user WHERE id = '$id' LIMIT 1";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_array($result);
?>

<!-- User info and post form -->
<table style="margin: auto; margin-bottom: 30px;">
    <tr>
        <td style="vertical-align: top;">
            <h2 style="margin: 0;"><?php echo $user['full_name'] ?></h2>
            <h4><?php echo $user['bio'] ?></h4>
        </td>
        <td style="padding-left: 15px;">
            <form id="homeform" method="post">
                <textarea required placeholder="Write something and post..." name="content" id="content"></textarea>
                <button name="submit" style="font-size: 18px;vertical-align: top;margin-left: 15px;" type="submit">POST</button>
            </form>
        </td>
    </tr>
</table>

<?php
// Fetch posts
$query = "SELECT post.id, content, full_name, user.id as user, created_at, COUNT(likes.post_id) as likes
        FROM post 
        JOIN user 
        ON user.id = post.user_id 
        LEFT JOIN likes ON likes.post_id = post.id
        WHERE post.user_id 
        IN (SELECT followed_user FROM follow WHERE follower_user = $id)
        OR post.user_id = $id
        GROUP BY post.id
        ORDER BY post.id DESC; ";
$result = mysqli_query($conn, $query);
?>

<!-- Render posts -->
<div class="posts">
    <?php if (mysqli_num_rows($result) === 0) : ?>
        <h3 id="no-posts" style="text-align: center;">Follow some users to see their posts</h3>
    <?php else : ?>
        <?php while ($row = mysqli_fetch_array($result)) : ?>
            <table>
                <thead>
                    <th style="text-align: left;"><a href="./profile.php?id=<?php echo $row['user'] ?>"><u><?php echo $row['full_name'] ?></u></a></th>
                    <th><?php echo $row['created_at'] ?></th>
                </thead>
                <tr>
                    <td style="width: 420px;">
                        <p><?php echo $row['content'] ?></p>
                        <?php
                        // Check if post is liked by user
                        $qry = "SELECT * FROM likes WHERE post_id = '" . $row['id'] . "' AND user_id = '$id' LIMIT 1";
                        $res = mysqli_query($conn, $qry);
                        ?>
                        <?php
                        if (mysqli_num_rows($res) === 0)
                            echo "<a style='font-size: 23px;' href='./like.php?post=" . $row['id'] . "&action=like'>LIKE " . $row['likes'] . "</a>";
                        else
                            echo "<a style='font-size: 23px; color: red' href='./like.php?post=" . $row['id'] . "&action=dislike'>LIKE " . $row['likes'] . "</a>" ?>
                    </td>
                </tr>
            </table>
        <?php endwhile; ?>
    <?php endif ?>
</div>

<script>
    if (!sessionStorage.getItem("userId"))
        location.href = "../index.php"

    // Post new status using ajax
    document.getElementById("homeform").addEventListener("submit", e => {
        e.preventDefault()
        const content = document.querySelector("#homeform textarea")
        const data = {
            content: content.value
        }
        content.value = ""
        const req = new XMLHttpRequest();
        req.open("POST", "./home.php");
        req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        req.responseType = "json";
        req.onreadystatechange = () =>
            req.readyState === 4 && handleResponse(req.response);
        req.send(JSON.stringify(data));
    })

    // Apppend the new post in homepage
    function handleResponse(res) {
        if (res.success) {
            if (document.getElementById("no-posts") !== null)
                document.getElementById("no-posts").remove()
            const post = `
                    <table>
                        <thead>
                            <th style="text-align: left;"><a href="./profile.php?id=${sessionStorage.getItem("userId")}"><u>${sessionStorage.getItem("username")}</u></a></th>
                            <th>${res.created_at}</th>
                        </thead>
                        <tr>
                            <td style="width: 420px;">
                                <p>${res.content}</p>
                                <a style='font-size: 23px;' href='./like.php?post=${res.id}&action=like'>LIKE 0</a>
                            </td>
                        </tr>
                    </table>
            `
            const posts = document.querySelector(".posts")
            posts.innerHTML = post + posts.innerHTML // Append on top of other posts
        }
    }
</script>

<?php
include_once '../includes/footer.php';
