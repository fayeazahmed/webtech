<?php
include_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    $data = json_decode(file_get_contents('php://input'), true);
    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];
    $password2 = $data['password2'];
    $bio = $data['bio'];
    $query = "SELECT * FROM user WHERE email = '$email' ";
    $result = mysqli_query($conn, $query);

    // Validation
    if (mysqli_num_rows($result) !== 0) {
        echo json_encode(array("success" => false, "m" => "Email already in use"));
        exit;
    }
    if (strpos($email, '@') === false) {
        echo json_encode(array("success" => false, "m" => "Invalid email"));
        exit;
    }
    if (strlen($password) < 6) {
        echo json_encode(array("success" => false, "m" => "Password must be at least 6 characters"));
        exit;
    }
    if ($password !== $password2) {
        echo json_encode(array("success" => false, "m" => "Passwords do not match"));
        exit;
    }

    $hashedPw = password_hash($password, PASSWORD_DEFAULT);
    $query = "INSERT INTO user (`email`, `full_name`, `password`, `bio`) VALUES ('$email', '$name', '$hashedPw', '$bio')";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $_SESSION['userId'] = mysqli_insert_id($conn);
        echo json_encode(array("success" => true, "userId" => $_SESSION['userId']));
    } else
        echo json_encode(array("success" => false, "m" => "Something went wrong"));

    exit;
}
include_once '../includes/header.php';

?>

<form id="regform" method="post" style="text-align: center;">
    <input required placeholder="Full name" type="text" name="name"><br>
    <input required placeholder="Your email" type="text" name="email"><br>
    <input required placeholder="Your password" type="password" name="password"><br>
    <input required placeholder="Password (again)" type="password" name="password2"><br>
    <input placeholder="Bio" type="text" name="bio"><br>
    <a href="../">Already have an account?</a>
    <input type="submit" value="Register" name="submit">
    <p style="color: red; font-size: 20px;"></p>
</form>

<script>
    // Redirecting if authenticated
    if (sessionStorage.getItem("userId"))
        location.href = "./home.php"

    // Form submission and handle response
    document.getElementById("regform").addEventListener("submit", e => {
        e.preventDefault()
        const name = document.querySelectorAll("#regform input")[0]
        const email = document.querySelectorAll("#regform input")[1]
        const password = document.querySelectorAll("#regform input")[2]
        const password2 = document.querySelectorAll("#regform input")[3]
        const bio = document.querySelectorAll("#regform input")[4]

        if (password.value !== password2.value) {
            document.querySelector("#regform p").innerText = "Passwords do not match!"
            return
        }
        const data = {
            "email": email.value,
            "name": name.value,
            "password": password.value,
            "password2": password2.value,
            "bio": bio.value
        }
        email.value = password.value = name.value = password2.value = bio.value = ""
        const req = new XMLHttpRequest();
        req.open("POST", "./register.php");
        req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        req.responseType = "json";
        req.onreadystatechange = () =>
            req.readyState === 4 && handleResponse(req.response);
        req.send(JSON.stringify(data));
    })

    function handleResponse(res) {
        if (!res.success)
            document.querySelector("#regform p").innerText = res.m
        else {
            sessionStorage.setItem("userId", res.userId)
            window.location.replace("./home.php")
        }
    }
</script>

<?php
include_once '../includes/footer.php';
