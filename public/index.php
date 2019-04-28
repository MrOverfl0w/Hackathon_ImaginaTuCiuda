<?php
if (PHP_SAPI == 'cli-server') {
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $url  = parse_url($_SERVER['REQUEST_URI']);
    $file = __DIR__ . $url['path'];
    if (is_file($file)) {
        return false;
    }
}

require __DIR__ . '/../vendor/autoload.php';

// Connect to DB
function getConnection() {
    $dbhost="127.0.0.1";
    $dbuser="cygnus";
    $dbpass="cygnuspass";
    $dbname="Hackathon_db";
    $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
}

session_start();

// Instantiate the app
$settings = require __DIR__ . '/../src/settings.php';
$app = new \Slim\App($settings);

// Set up dependencies
$dependencies = require __DIR__ . '/../src/dependencies.php';
$dependencies($app);

// Register middleware
$middleware = require __DIR__ . '/../src/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/../src/routes.php';
$routes($app);

// Run app
$app->run();

//Mostrar id de todos los usuarios
function retornarUsuarios($response){
    $sql = "SELECT CC FROM Usuario";
    try {
        $stmt = getConnection()->query($sql);
        $usuarios = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return json_encode($usuarios);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Ver información de un usuario
function infoUsuario($response){
    $id = $response->getAttribute('ID');
    $sql = "SELECT CC,Nombre,Correo,Celular,localidad FROM Usuario WHERE CC=:id";
    try {
        $stmt = getConnection()->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $info = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return json_encode($info);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Ver propuestas
function retornarPropuestas($response){
    $sql = "SELECT Propuesta.Descripcion, Reto.Nombre FROM Propuesta LEFT JOIN Reto ON Propuesta.IDReto=Reto.IDReto";
    try {
        $stmt = getConnection()->query($sql);
        $propuestas = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return json_encode($propuestas);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Ver propuestas específicas del usuario
function retornarPropuestasUsuario($response){
    $id = $response->getAttribute("ID");
    $sql = "SELECT Propuesta.Descripcion, Reto.Nombre FROM (Propuesta LEFT JOIN Reto ON Propuesta.IDReto=Reto.IDReto)
    INNER JOIN Usuario ON Propuesta.IDUsuario=Usuario.CC WHERE Usuario.CC=:id";
    try {
        $stmt = getConnection()->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $propuestas = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return json_encode($propuestas);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Crear usuario
function crearUsuario($request){
    
    $sql = "INSERT INTO Usuario (CC, Nombre, Correo, Contrasena, Celular, localidad, FechaNacimiento)
     VALUES (:id, :nombre, :correo, :contrasena, :celular, :localidad, :fechaNacimiento)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $stmt->bindParam("nombre", $_POST["Nombre"]);
        $stmt->bindParam("correo", $_POST["Correo"]);
        $stmt->bindParam("contrasena", $_POST["Contrasena"]);
        $stmt->bindParam("celular", $_POST["Celular"]);
        $stmt->bindParam("localidad", $_POST["Localidad"]);
        $stmt->bindParam("fechaNacimiento", $_POST["FechaNacimiento"]);
        $stmt->execute();
        $db = null;
        echo json_encode("Ok");
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Actualiza la información del usuario
function actualizarUsuario($request){
    
    $sql = "UPDATE Usuario SET Correo=:correo, Contrasena=:contrasena, Celular=:celular
     WHERE CC=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $stmt->bindParam("correo", $_POST["Correo"]);
        $stmt->bindParam("contrasena", $_POST["Contrasena"]);
        $stmt->bindParam("celular", $_POST["Celular"]);
        $stmt->execute();
        $db = null;
        echo json_encode("Ok");
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Borrar usuario
function eliminarUsuario($request) {
    $id = $request->getAttribute('ID');
    $sql = "DELETE FROM Usuario WHERE CC=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
        echo '{"error":{"text":"se eliminó el usuario"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Validar credenciales del usuario
function validarUsuario($request){
    
    $sql = "SELECT Contrasena FROM Usuario WHERE CC=:id";
    try {
        $stmt = getConnection()->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $pass = $_POST["Contrasena"];
        $stmt->execute();
        $contrasena = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        if ($pass == $contrasena):
            return json_encode(true);
        endif;
        return json_encode(false);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Crear propuesta
function crearPropuesta($request){
    
    $sql = "INSERT INTO Propuesta (IDPropuesta, Descripcion, IDReto, IDUsuario)
     VALUES (:id, :descripcion, :idReto, :idUsuario)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $stmt->bindParam("descripcion", $_POST["Descripcion"]);
        $stmt->bindParam("idReto", $_POST["IDReto"]);
        $stmt->bindParam("idUsuario", $_POST["IDUsuario"]);
        $stmt->execute();
        $db = null;
        echo json_encode("Ok");
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Crear multimedia propuesta
function crearMultimediaPropuesta($request){
    
    $sql = "INSERT INTO Multimedia (IDMultimedia, Tipo, Ruta)
     VALUES (:id, :tipo, :ruta)";
    try {
        $ruta = $_POST["ID"] . "_" . $_POST["NombreArchivo"];
        include_once("class_imgUpldr.php");
        $subir= new imgUpldr;
        $subir->_name = $ruta;
        $subir->init($_FILES['multimedia']);

        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $stmt->bindParam("tipo", $_POST["Tipo"]);
        $stmt->bindParam("ruta", $ruta);
        $stmt->execute();

        echo json_encode("Ok");

        $sql = "INSERT INTO `Multimedia-Propuesta` (IDMultimedia, IDPropuesta)
         VALUES (:id, :idPropuesta)";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $stmt->bindParam("idPropuesta", $_POST["IDPropuesta"]);
        $stmt->execute();

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Actualiza propuesta
function actualizarPropuesta($request){
    
    $sql = "UPDATE Propuesta SET Descripcion=:descripcion WHERE IDPropuesta=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $stmt->bindParam("descripcion", $_POST["Descripcion"]);
        $stmt->execute();
        $db = null;
        echo json_encode("Ok");
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Borrar propuesta
function eliminarPropuesta($request) {
    $id = $request->getAttribute('ID');
    $sql = "DELETE FROM Propuesta WHERE IDPropuesta=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
        echo '{"error":{"text":"se eliminó la propuesta"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Borrar multimedia propuesta
function eliminarMultimediaPropuesta($request) {
    $id = $request->getAttribute('ID');
    $sql = "SELECT Ruta FROM `Multimedia` WHERE IDMultimedia=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $ruta = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        unlink($ruta);

        $sql = "DELETE FROM `Multimedia-Propuesta` WHERE IDMultimedia=:id";
        $sql2 = "DELETE FROM `Multimedia` WHERE IDMultimedia=:id";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $stmt2 = $db->prepare($sql2);
        $stmt2->bindParam("id", $id);
        $stmt2->execute();
        $db = null;
        echo '{"error":{"text":"se eliminó el archivo"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Ver comentarios de una propuesta
function retornarComentarios($response){
    $id = $response->getAttribute("ID");
    $sql = "SELECT * FROM Comentario WHERE IDPropuesta=:id";
    try {
        $stmt = getConnection()->prepare($sql);
        $stmt->bindParam("id", $id);
        $usuarios = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return json_encode($usuarios);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Escribir un nuevo comentario
function crearComentarios($request){
    
    $sql = "INSERT INTO Comentario (IDComentario, Texto, IDUsuario, IDPropuesta)
     VALUES (:id, :texto, :idUsuario, :idPropuesta)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $stmt->bindParam("texto", $_POST["Texto"]);
        $stmt->bindParam("idUsuario", $_POST["IDUsuario"]);
        $stmt->bindParam("idPropuesta", $_POST["IDPropuesta"]);
        $stmt->execute();
        $db = null;
        echo json_encode("Ok");
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Editar un comentario
function actualizarComentarios($request){
    
    $sql = "UPDATE Comentario SET Texto=:text WHERE IDComentario=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $stmt->bindParam("texto", $_POST["Texto"]);
        $stmt->execute();
        $db = null;
        echo json_encode("Ok");
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Eliminar un comentario
function eliminarComentarios($request){
    $id = $request->getAttribute('ID');
    $sql = "DELETE FROM Comentario WHERE IDComentario=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
        echo '{"error":{"text":"se eliminó el comentario"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Devuelve información de todos los votos
function retornarVotos($response){
    $sql = "SELECT * FROM Voto";
    try {
        $stmt = getConnection()->query($sql);
        $stmt->execute();
        $info = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return json_encode($info);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Cantidad de votos de una propuesta
function votosPropuesta($response){
    $id = $response->getAttribute('ID');
    $sql = "SELECT count(IDPropuesta) FROM Voto WHERE IDPropuesta=:id";
    try {
        $stmt = getConnection()->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $info = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return json_encode($info);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Votos de un usuario
function votosUsuario($response){
    $id = $response->getAttribute('ID');
    $sql = "SELECT count(IDUsuario) FROM Voto WHERE IDUsuario=:id";
    try {
        $stmt = getConnection()->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $info = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return json_encode($info);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Votar por una propuesta
function votar($request){
    $sql = "INSERT INTO Voto (IDVoto, IDUsuario, IDPropuesta)
     VALUES (:id, :idUsuario, :idPropuesta)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $stmt->bindParam("idUsuario", $_POST["IDUsuario"]);
        $stmt->bindParam("idPropuesta", $_POST["IDPropuesta"]);
        $stmt->execute();
        $db = null;
        echo json_encode("Ok");
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Información del administrador
function infoAdmin($response){
    $sql = "SELECT IDUsuario, Correo FROM Admin";
    try {
        $stmt = getConnection()->query($sql);
        $stmt->execute();
        $info = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return json_encode($info);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Modificar información del administrador
function actualizarAdmin($request){
    $sql = "UPDATE Admin SET Contrasena=:contrasena, Correo=:correo WHERE IDUsuario=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $stmt->bindParam("contrasena", $_POST["Contrasena"]);
        $stmt->bindParam("correo", $_POST["Correo"]);
        $stmt->execute();
        $db = null;
        echo json_encode("Ok");
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Mostrar retos
function retornarRetos($response){
    $sql = "SELECT * FROM Reto";
    try {
        $stmt = getConnection()->query($sql);
        $stmt->execute();
        $info = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        return json_encode($info);
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Añadir un reto
function crearReto($request){
    $sql = "INSERT INTO Reto (IDReto, Descripcion, Nombre)
     VALUES (:id, :descripcion, :nombre)";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $stmt->bindParam("descripcion", $_POST["Descripcion"]);
        $stmt->bindParam("nombre", $_POST["Nombre"]);
        $stmt->execute();
        $db = null;
        echo json_encode("Ok");
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Crear multimedia para un reto
function crearMultimediaReto($request){
    
    $sql = "INSERT INTO Multimedia (IDMultimedia, Tipo, Ruta)
     VALUES (:id, :tipo, :ruta)";
    try {
        $ruta = $_POST["ID"] . "_" . $_POST["NombreArchivo"];
        include_once("class_imgUpldr.php");
        $subir= new imgUpldr;
        $subir->_name = $ruta;
        $subir->init($_FILES['multimedia']);

        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $stmt->bindParam("tipo", $_POST["Tipo"]);
        $stmt->bindParam("ruta", $ruta);
        $stmt->execute();

        echo json_encode("Ok");

        $sql = "INSERT INTO `Multimedia-Reto` (IDMultimedia, IDReto)
         VALUES (:id, :idReto)";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $stmt->bindParam("idReto", $_POST["IDReto"]);
        $stmt->execute();

    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Modificar un reto
function actualizarReto($request){
    $sql = "UPDATE Reto SET Descripcion=:descripcion, Nombre=:nombre WHERE IDReto=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $_POST["ID"]);
        $stmt->bindParam("descripcion", $_POST["Descripcion"]);
        $stmt->bindParam("nombre", $_POST["Nombre"]);
        $stmt->execute();
        $db = null;
        echo json_encode("Ok");
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Eliminar un reto
function eliminarReto($request) {
    $id = $request->getAttribute('ID');
    $sql = "DELETE FROM Reto WHERE IDReto=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $db = null;
        echo '{"error":{"text":"se eliminó el reto"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Borrar multimedia de un reto
function eliminarMultimediaReto($request) {
    $id = $request->getAttribute('ID');
    $sql = "SELECT Ruta FROM `Multimedia` WHERE IDMultimedia=:id";
    try {
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $ruta = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;
        
        unlink($ruta);

        $sql = "DELETE FROM `Multimedia-Reto` WHERE IDMultimedia=:id";
        $sql2 = "DELETE FROM `Multimedia` WHERE IDMultimedia=:id";
        $db = getConnection();
        $stmt = $db->prepare($sql);
        $stmt->bindParam("id", $id);
        $stmt->execute();
        $stmt2 = $db->prepare($sql2);
        $stmt2->bindParam("id", $id);
        $stmt2->execute();
        $db = null;
        echo '{"error":{"text":"se eliminó el archivo"}}';
    } catch(PDOException $e) {
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Muestra si las votaciones están habilitadas o no
function estadoVotaciones($response){
    $sql = "SELECT Estado FROM Admin";
    try{
        $stmt = getConnection()->query($sql);
        $db = null;
        $estado = $stmt->fetchAll(PDO::FETCH_OBJ);
        return json_encode($estado);
    }catch(PDOException $e){
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Habilitar votaciones
function activarVotaciones($request){
    $sql = "UPDATE Admin SET Estado='Habilitadas'";
    try{
        $stmt = getConnection()->query($sql);
        $db = null;
        
        return json_encode("Ok");
    }catch(PDOException $e){
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//Deshabilitar votaciones
function desactivarVotaciones($request){
    $sql = "UPDATE Admin SET Estado='Deshabilitadas'";
    try{
        $stmt = getConnection()->query($sql);
        $db = null;
        
        return json_encode("Ok");
    }catch(PDOException $e){
        echo '{"error":{"text":'. $e->getMessage() .'}}';
    }
}

//
//
//