<?php
include_once 'includes/db.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['email'];
    $query = "SELECT * FROM user WHERE email = '$email' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 0) {
        echo json_encode(array("success" => false, "m" => "Email does not exist"));
        exit;
    }

    $row = mysqli_fetch_array($result);
    if (password_verify($data['password'], $row['password'])) {
        $_SESSION['userId'] = $row['id'];
        echo json_encode(array("success" => true, "userId" => $row['id'], "userName" => $row['full_name']));
    } else
        echo json_encode(array("success" => false, "m" => "Incorrect password"));
    exit;
}
include_once 'includes/header.php';
?>
<h3>This is a social media platform without any media. Post content and follow others to see theirs. Login to continue - </h3>

<!-- Login form -->
<form id="loginform" method="post" style="text-align: center;">
    <input required placeholder="Your email" type="text" name="email"><br>
    <input required placeholder="Your password" type="password" name="password"><br>
    <a href="pages/register.php">Create an account</a>
    <input style="margin-left: 77px;" type="submit" value="Login" name="submit">
    <p style="color: red; font-size: 20px;"></p>
</form>

<script>
    // Redirecting if authenticated
    if (sessionStorage.getItem("userId"))
        location.href = "./pages/home.php"

    // Form submission and handle response
    document.getElementById("loginform").addEventListener("submit", e => {
        e.preventDefault()
        const email = document.querySelectorAll("#loginform input")[0]
        const password = document.querySelectorAll("#loginform input")[1]
        const data = {
            "email": email.value,
            "password": password.value
        }
        email.value = password.value = ""
        const req = new XMLHttpRequest();
        req.open("POST", "./index.php");
        req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        req.responseType = "json";
        req.onreadystatechange = () =>
            req.readyState === 4 && handleResponse(req.response);
        req.send(JSON.stringify(data));
    })

    function handleResponse(res) {
        if (!res.success)
            document.querySelector("#loginform p").innerText = res.m
        else {
            sessionStorage.setItem("userId", res.userId)
            sessionStorage.setItem("username", res.userName)
            window.location.replace("./pages/home.php")
        }
    }
</script>
<?php
include_once 'includes/footer.php';
