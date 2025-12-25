// logout.php
<?php
session_start();
session_destroy(); // Destroys all session data
header("Location: /login"); // Redirect to login page
exit();
?>