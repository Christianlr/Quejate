<?php
require '../twig/vendor/autoload.php';
require_once "../model/UsuarioModel.php";
require_once "../model/IncidenciasModel.php";
require_once "../model/LogsModel.php";

session_start();

function comprobarFallos($usuario, &$campos) {
    $fallos = false;
    if (empty($campos['nombre'])) {
        $campos['nombre'] = ' ';
        $fallos = true;
    }
    if (empty($campos['apellidos'])) {
        $campos['apellidos'] = ' ';
        $fallos = true;
    }

    if (!filter_var($campos['email'], FILTER_VALIDATE_EMAIL) || $usuario->existeCorreo($campos['email'])) {
        $campos['email'] = 'incorrecto';
        $fallos = true;
    }

    if (!preg_match("/^(\+34)?[ -]?(6|7|9)([0-9]){8}$/", $campos['telefono']) && !empty($campos['telefono'])) {
        $campos['telefono'] = 'incorrecto';
        $fallos = true;
    }

    if ((empty($campos['password']) || empty($_POST['claveConfirmacion'])) ||
        ($campos['password'] != $_POST['claveConfirmacion']) ||
        (strlen($campos['password']) < 8)) {
        $campos['password'] = 'mal';
        $fallos = true;
    }
    return $fallos;

}

/* Cargamos twig para usar el render */
$loader = new \Twig\Loader\FilesystemLoader('../view/html');
$twig = new \Twig\Environment($loader);

//Si no es administrador se redirige al inicio
if ($_SESSION['datosUsuario']['rol'] != 'administrador') {
    header('Location: inicioController.php');
}

$usuario = new UsuarioModel();
$incidencia = new IncidenciasModel();
$log = new LogsModel();

$confirmacion = null;
$campos = null;
$archivoRender = 'accionGestionUsuarios.html';
if (isset($_POST['crearUsuario'])) {
    foreach ($_POST as $clave => $valor) {
        if ($clave != 'crearUsuario' && $clave != 'claveConfirmacion') {
            if ($clave == 'claveNueva') 
                $campos['password'] = $_POST[$clave];
            else 
                $campos[$clave] = $_POST[$clave];
        }
    }
    if (isset($_FILES['examinar']) && $_FILES['examinar']['error'] === UPLOAD_ERR_OK) 
        $campos['foto'] = file_get_contents($_FILES['examinar']['tmp_name']);
    else 
        $campos['foto'] = file_get_contents('../view/img/defaultProfile.png');
    
    $fallos = comprobarFallos($usuario, $campos);

    if ($fallos) {
        $confirmacion = false;
    }
    else {
        $confirmacion = true;
        $usuario->crearUsuario($campos);
        $log->setCrearUsuario(date('Y-m-d H:i:s'), $_SESSION['datosUsuario']['email'], $campos['email']);
    }
    $campos['foto'] = base64_encode($campos['foto']);
}
else if (isset($_POST['confirmarCreacionUsuario'])) {
    $archivoRender = 'confirmacionesUsuario.html';
}


echo $twig->render($archivoRender, [
    'extends' => 'editarUsuario.html',
    'css' => '../view/css/editarUsuario.css',
    'ranking' => $_SESSION['ranking'],
    'datosUsuario' => $_SESSION['datosUsuario'],
    'datosNuevos' => $campos,
    'tipo' => 'crear',
    'confirmacion' => $confirmacion
    
]);
?>