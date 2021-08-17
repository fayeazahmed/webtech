<?php
session_start();

// remove all session variables
session_unset();

// destroy the session
session_destroy();
?>
<script>
    sessionStorage.removeItem("userId")
    location.href = "../index.php"
</script>