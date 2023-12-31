<?php
include_once '../db.php';

if(!isset($_SESSION['username'])){

    header('location: ../login');

    exit;

}

?>