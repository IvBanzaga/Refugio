<?php

/*
    Pagina que redirige en caso de no estar logueado a la página principal de login.php
    Para ello comprueba si la variable de SESSION['userId'] creada en el login.php existe.
*/

if (!isset($_SESSION['userId'])) {
    header('Location: login.php');
    exit;
}

?>