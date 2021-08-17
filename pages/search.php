<?php
include_once '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();
    $data = json_decode(file_get_contents('php://input'), true);
    $keyword = $data['query'];
    $query = "SELECT id, full_name, email FROM user WHERE email LIKE '%$keyword%' OR full_name LIKE '%$keyword%' ";
    $result = mysqli_query($conn, $query);

    // Return false if no result found
    if (mysqli_num_rows($result) === 0) {
        echo json_encode(array("success" => false, "m" => "No user found with matching query"));
        exit;
    }

    $response = array();
    while ($row = mysqli_fetch_array($result))
        $response[] = $row;

    echo json_encode(array("success" => true, "rows" => $response));
    exit;
}
include_once '../includes/header.php';
?>

<form id="searchform" style="text-align: center; margin-bottom: 30px">
    <label style="font-size: 20px; margin-right: 25px;" for="keyword">Search for users:</label>
    <input type="text" name="keyword" id="keyword" placeholder="Enter a name or email">
    <input type="submit" hidden>
</form>

<h3 style="text-align: center;"></h3>

<table style="margin: auto; display: none">
    <tr>
        <td style="font-size: 19px;">Name</td>
        <td style="padding-left: 10px; font-size: 19px;"><i>Email</i></td>
    </tr>
</table>
<script>
    // Handle search request
    document.getElementById("searchform").addEventListener("submit", e => {
        e.preventDefault()
        const query = document.querySelector("#searchform input")
        const data = {
            query: query.value
        }
        query.value = ""
        const req = new XMLHttpRequest();
        req.open("POST", "./search.php");
        req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        req.responseType = "json";
        req.onreadystatechange = () =>
            req.readyState === 4 && handleResponse(req.response);
        req.send(JSON.stringify(data));
    })

    // Append results in document
    function handleResponse(res) {
        if (!res.success)
            document.querySelector("h3").textContent = res.m
        else {
            const results = document.querySelector("table")
            results.style.display = "table"
            res.rows.forEach(row => {
                const tr = document.createElement("tr")
                tr.innerHTML = `
                    <tr>
                        <td style="font-size: 19px;"><a href="./profile.php?id=${row.id}"><b>${row.full_name}</b></a></td>
                        <td style="padding-left: 10px; font-size: 19px;"><i>${row.email}</i></td>
                    </tr>
                `
                results.appendChild(tr);
            });
        }
    }
</script>

<?php
include_once '../includes/footer.php';
