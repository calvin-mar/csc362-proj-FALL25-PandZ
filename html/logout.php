<?php
//Logs user out of P&Z system
session_start();
session_destroy();
header('Location: login.php');
exit();
?>