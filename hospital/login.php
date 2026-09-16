<?php
// This role-specific login page has been retired in favor of the single
// central login at the project root — everyone (admin, hospital, parent)
// signs in there and gets routed to their own dashboard automatically.
header("Location: ../login.php");
exit;
