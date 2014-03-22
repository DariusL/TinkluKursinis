<?php
include("session.php");
global $session, $database;
if(isset($session) && $session->logged_in && !$session->isAdmin()){
    $lat = lcg_value() - 0.5 + 54.9;
    $lng = lcg_value() - 0.5 + 23.9;
    $database->addLocation($session->user_id, $lat, $lng);
}
header("Location: ../index.php");
?>