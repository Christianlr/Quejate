<?php
require '../twig/vendor/autoload.php';
require_once "../model/UsuarioModel.php";
require_once "../model/IncidenciasModel.php";

session_start();

/* Cargamos twig para usar el render */
$loader = new \Twig\Loader\FilesystemLoader('../view/html');
$twig = new \Twig\Environment($loader);

//Si no es administrador se redirige al inicio
if ($_SESSION['datosUsuario']['rol'] != 'administrador') {
    header('Location: inicioController.php');
}

$usuario = new UsuarioModel();
$incidencia = new IncidenciasModel();

$todo = $usuario->getAll();
foreach ($todo as &$parte)
    $parte['nombreCompleto'] = $parte['nombre'] . " " . $parte['apellidos'];

echo $twig->render('accionGestionUsuarios.html', [
    'ranking' => $_SESSION['ranking'],
    'datosUsuario' => $_SESSION['datosUsuario'],
    'datosCompletosUsuarios' => $todo,
    'extends' => 'gestionUsuarios.html'
]);
?>