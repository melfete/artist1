<?php
session_start();

if (!isset($_SESSION["test"])) {
    $_SESSION["test"] = "Session funktioniert!";
} else {
    echo $_SESSION["test"];
}
?>
