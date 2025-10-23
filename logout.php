<?php

/*
	Realiza la destrucción de la sesión creada y se redirige al formulario de login.php
*/

require 'conexion.php';
session_destroy();

header('Location: login.php');
exit;
?>