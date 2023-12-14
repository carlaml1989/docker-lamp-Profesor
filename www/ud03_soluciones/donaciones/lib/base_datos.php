<?php

function get_conexion()
{
    $servername = 'db';
    $username = 'root';
    $password = 'test';

    try {
        $conexion = new PDO("mysql:host=$servername", $username, $password);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conexion;
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}

function seleccionar_bd_donacion($conexion)
{
    $sql = "use donacion";
    ejectutar_consulta($conexion, $sql);
}

function ejectutar_consulta($conexion, $sql)
{
    try {
        $conexion->query($sql);
    } catch (PDOException $e) {
        echo $e->getMessage();
    }
}


function crear_bd_donacion($conexion)
{
    $sql = "CREATE DATABASE IF NOT EXISTS donacion";
    ejectutar_consulta($conexion, $sql);
}

function crear_tabla_administradores($conexion)
{
    $sql = "CREATE TABLE IF NOT EXISTS administradores(
        nombre VARCHAR(50) PRIMARY KEY ,
        contrasinal varchar(200) NOT NULL
        )";
    ejectutar_consulta($conexion, $sql);
}

function crear_tabla_donantes($conexion)
{

    $sql = "CREATE TABLE if NOT EXISTS donantes(
        id INT(6) AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(30) NOT NULL,
        apellidos VARCHAR(30) NOT NULL,
        edad INT(3) NOT NULL,
        grupoSanguineo VARCHAR(3) NOT NULL,
        codPostal INT(5) NOT NULL,
        telefonoMovil INT(9) NOT NULL
        )";
    ejectutar_consulta($conexion, $sql);
}

function crear_tabla_historico($conexion)
{

    $sql = "CREATE TABLE IF NOT EXISTS historico(
    idDonante INT(6) NOT NULL,
    fechaDonacion DATE NOT NULL,
    proximaDonacion DATE NOT NULL,
    PRIMARY KEY (idDonante, fechaDonacion),
    FOREIGN KEY (idDonante) REFERENCES donantes(id) ON DELETE CASCADE
    )";
    ejectutar_consulta($conexion, $sql);
}

function dar_alta_donante($conexion, $nombre, $apellidos, $edad, $grupoSanguineo, $codPostal, $movil)
{
    $consulta = $conexion -> prepare("INSERT INTO donantes (nombre,apellidos,edad,grupoSanguineo,codPostal,telefonoMovil) VALUES (:nombre,:apellidos,:edad,:grupo,:codigoP,:movil)");
    $consulta->bindParam(":nombre", $nombre);
    $consulta->bindParam(":apellidos", $apellidos);
    $consulta->bindParam(":edad", $edad);
    $consulta->bindParam(":grupo", $grupoSanguineo);
    $consulta->bindParam(":codigoP", $codPostal);
    $consulta->bindParam(":movil", $movil);
    $consulta->execute();
}

function buscar_donantes_codigopostal($conexion, $codPostal)
{
    $consulta = $conexion->prepare("SELECT * FROM donantes WHERE codPostal = :codigoP");
    $consulta->bindParam(':codigoP', $codPostal);
    return $consulta->execute();
}

function get_donantes($conexion)
{
    $consulta = $conexion->prepare("SELECT * FROM donantes");
    $consulta->execute();
    return $consulta;
}

function dar_alta_administrador($conexion, $nombre, $contrasinal)
{
    $consulta = $conexion->prepare("INSERT INTO administradores (nombre, contrasinal) VALUES (:nombre,:contrasinal)");
    $consulta->bindParam(':nombre', $nombre);
    $consulta->bindParam('contrasinal', $contrasinal);
    return $consulta->execute();
}

function dar_alta_donacion($conexion, $idDonante, $fechaDonacion)
{
    $ultima_donacion = get_ultima_donacion($conexion, $idDonante);

    
    if (!$ultima_donacion || ($ultima_donacion <  $fechaDonacion)) {
        $fechaProximaDonacion = date("Y-m-d", strtotime($fechaDonacion . "+4 month"));
        $consulta = $conexion->prepare("INSERT INTO historico (idDonante,fechaDonacion,proximaDonacion) VALUES (:idDonante,:fechaDonacion,:proximaDonacion)");
        $consulta->bindParam(":idDonante", $idDonante);
        $consulta->bindParam(":fechaDonacion", $fechaDonacion);
        $consulta->bindParam(":proximaDonacion", $fechaProximaDonacion);
        $consulta->execute();
        return true;
    } else {
        return false;
    }
}

function eliminar_donante($conexion, $idDonante)
{
    $consulta = $conexion->prepare("DELETE d FROM donantes d LEFT JOIN historico h ON d.id = h.idDonante where d.id =$idDonante");
    return $consulta->execute();
}
function get_donaciones($conexion, $idDonante)
{
    $consulta = $conexion->prepare("SELECT donantes.nombre, donantes.apellidos, historico.fechaDonacion, historico.proximaDonacion
                                                          FROM donantes, historico 
                                                          WHERE donantes.id=historico.idDonante AND donantes.id=$idDonante
                                                          ORDER BY historico.fechaDonacion DESC");
    $consulta->execute();
    return $consulta;
}
function get_ultima_donacion($conexion, $idDonante)
{
    $proxima_donacion = null;
    $consulta = $conexion->prepare("SELECT donantes.nombre, donantes.apellidos, historico.fechaDonacion, historico.proximaDonacion
                                                          FROM donantes, historico 
                                                          WHERE donantes.id=historico.idDonante AND donantes.id=$idDonante
                                                          ORDER BY historico.fechaDonacion DESC LIMIT 1");
    $consulta->execute();
    while ($fila = $consulta->fetch()) {
        $proxima_donacion = $fila['proximaDonacion'];
    }

    return $proxima_donacion;
}

function cerrar_conexion($conexion)
{
    $conexion = null;
}
