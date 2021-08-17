<?php
include_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    if (!isset($_SESSION['userId'])) {
        header('location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    $method = $data['method'];
    $content = $data['content'];
    $query = "SELECT * FROM post WHERE id = '$id' LIMIT 1";
    $result = mysqli_query($conn, $query);
    if ($method === 'update')
        $query = "UPDATE post SET `content` = '$content' WHERE id = '$id'";
    else
        $query = "DELETE FROM post WHERE id = '$id'";
    mysqli_query($conn, $query);
    echo json_encode(array("success" => true));
    exit;
}

include_once '../includes/header.php';

if (!isset($_GET['id']) || !isset($_SESSION['userId'])) {
    header('location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

$id = $_GET['id'];
$user_id = $_SESSION['userId'];
$query = "SELECT * FROM post WHERE id = '$id' LIMIT 1";
$result = mysqli_query($conn, $query);
if (mysqli_num_rows($result) === 0) {
    header('location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
$post = mysqli_fetch_array($result);
// Redirecting back if post doesn't belong to user
if ($post['user_id'] !== $user_id) {
    header('location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}
?>

<h3>Post id #<span><?php echo $post['id'] ?></span></h3>
<form class="editforms" method="post">
    <textarea required placeholder="Update post..." style="resize: none; padding: 7px;" name="content" id="content" cols="52" rows="5"><?php echo $post['content'] ?></textarea>
    <input type="submit" name="update" style="font-size: 18px;vertical-align: top;margin-left: 15px;" type="submit" value="Update">
</form>
<form class="editforms" method="post">
    <input style="color: red;" type="submit" name="delete" value="Confirm delete">
</form>

<script>
    // Update or delete post through ajax request
    document.querySelectorAll(".editforms").forEach(form => form.addEventListener("submit", e => {
        e.preventDefault()
        const content = document.querySelector(".editforms textarea")
        const data = {
            content: content.value,
            method: form.querySelector("input").name,
            id: document.querySelector("h3 span").textContent
        }
        const req = new XMLHttpRequest();
        req.open("POST", "./delete.php");
        req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        req.responseType = "json";
        req.onreadystatechange = () =>
            req.readyState === 4 && window.location.replace("./profile.php");
        req.send(JSON.stringify(data));
    }))
</script>

<?php
include "../includes/footer.php";
