<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    $container = $app->getContainer();
    $app->get('/usuarios', 'retornarUsuarios');
    $app->get('/usuarios/{ID}', 'infoUsuario');
    $app->post('/usuarios/crear', 'crearUsuario');
    $app->put('/usuarios/actualizar', 'actualizarUsuario');
    $app->delete('/usuarios/eliminar', 'eliminarUsuario');
    $app->post('/usuarios/validar', 'validarUsuario');
    $app->get('/propuestas', 'retornarPropuestas');
    $app->get('/propuestas/usuario/{ID}', 'retornarPropuestasUsuario');
    $app->post('/propuestas/crear', 'crearPropuesta');
    $app->post('/propuestas/crear/multimedia', 'crearMultimediaPropuesta');
    $app->put('/propuestas/actualizar', 'actualizarPropuesta');
    $app->delete('/propuestas/eliminar', 'eliminarPropuesta');
    $app->delete('/propuestas/eliminar/multimedia', 'eliminarMultimediaPropuesta');
    $app->get('/propuestas/comentarios/{ID}', 'retornarComentarios');
    $app->post('/propuestas/comentarios/crear', 'crearComentarios');
    $app->put('/propuestas/comentarios/actualizar', 'actualizarComentarios');
    $app->delete('/propuestas/comentarios/eliminar', 'eliminarComentarios');
    $app->get('/votos','retornarVotos');
    $app->get('/votos/propuesta/{ID}','votosPropuesta');
    $app->get('/votos/usuario/{ID}','votosUsuario');
    $app->post('/votos/votar','votar');

    $app->get('/admin/info','infoAdmin');
    $app->put('/admin/actualizar','actualizarAdmin');
    $app->get('/retos','retornarRetos');
    $app->post('/retos/crear','crearReto');
    $app->post('/retos/crear/multimedia','crearMultimediaReto');
    $app->put('/retos/actualizar','actualizarReto');
    $app->delete('/retos/eliminar','eliminarReto');
    $app->delete('/retos/eliminar/multimedia','eliminarMultimediaReto');
    $app->get('/votos/estado','estadoVotaciones');
    $app->post('/votos/activar','activarVotaciones');
    $app->post('/votos/desactivar','desactivarVotaciones');

    $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
        // Sample log message
        $container->get('logger')->info("Slim-Skeleton '/' route");

        // Render index view
        return $container->get('renderer')->render($response, 'index.phtml', $args);
    });

    

};
