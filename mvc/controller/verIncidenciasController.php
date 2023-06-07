<?php
require '../twig/vendor/autoload.php';
require_once "../model/UsuarioModel.php";
require_once "../model/IncidenciasModel.php";
require_once "../model/FotosIncidenciasModel.php";
require_once "../model/ComentariosModel.php";

session_start();
unset($_SESSION['incidenciaActual']);
/* Cargamos twig para usar el render */
$loader = new \Twig\Loader\FilesystemLoader('../view/html');
$twig = new \Twig\Environment($loader);

/* Obtencion de los rankings */
#---------------------------------------------#

$usuario = new UsuarioModel();
$incidencia = new IncidenciasModel();
$fotos = new FotosIncidenciasModel();
$comentarios = new ComentariosModel();

$queryString = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
parse_str($queryString, $params);
if (!isset($params['email'])) 
    $todasIncidencias = $incidencia->getAll();
else
    $todasIncidencias = $incidencia->getAllByUser($params['email']);

foreach ($todasIncidencias as &$parte) {
    $nombreCompleto = $usuario->getNombreApellidos($parte['id_usuario']);
    $parte['nombreUsuario'] = $nombreCompleto['nombre'] . " " . $nombreCompleto['apellidos'];
    $parte['fotos'] = $fotos->getFotosById($parte['id']);

    $parte['comentarios'] = $comentarios->getAllById($parte['id']);
    if (!empty($parte['comentarios']))
        foreach ($parte['comentarios'] as &$c) {
            $nombreCompleto = $usuario->getNombreApellidos($c['id_usuario']);
            $c['nombreUsuario'] = $nombreCompleto['nombre'] . " " . $nombreCompleto['apellidos'];
        }
}
$_SESSION['todasIncidencias'] = $todasIncidencias;

echo $twig->render('criteriosBusqueda.html', [
    'css' => '../view/css/editarUsuario.css',
    'total' => $_SESSION['rankingAdd'][0],
    'nombresRanking' => $_SESSION['rankingAdd'][1],
    'datosUsuario' => $_SESSION['datosUsuario'],
    'todasIncidencias' => $todasIncidencias,
]);
?>