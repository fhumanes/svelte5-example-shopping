<?php

include_once __DIR__.'/Config.php';                 // Configuration Rest Api
include_once __DIR__.'/Function.php';               // General Function

require_once __DIR__.'/DBF_session.php';            // Funciones específicas según tablas
require_once __DIR__.'/DBF_identification.php';
require_once __DIR__.'/DBF_groupUser.php';
require_once __DIR__.'/DBF_groupSpecial.php';
require_once __DIR__.'/DBF_general.php';
require_once __DIR__.'/DBF_product.php';
require_once __DIR__.'/DBF_user.php';

/**
 *
 * @About:      Gestión de Compras
 * @File:       DbFunctions
 * @Date:       $Date:$ sep 2025
 * @Version:    $Rev:$ 1.0
 * @Developer:  fernando humanes
 **/

/*
    use Psr\Http\Message\ResponseInterface as Response;
    use Psr\Http\Message\ServerRequestInterface as Request;
    
    use PHPMailer\PHPMailer\PHPMailer;   // PHPMailer
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;
 */   

class DbFunctions
{

    private $db;

    function __construct()
    {
        $dbHost = DB_HOST;
        $dbName = DB_NAME;

        $dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $options);
            $this->db = $pdo;
        } catch (PDOException $e) {
            custom_error(1000,"Error en DbFunction: ".print_r($e, true));  // to Debug
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
         
    }

     use Identification;              // Grupo de funcionaes de identificacion   
     use Session;                     // Grupo de funcionaes de Gestión de la Sesión "particular" del sistema
     use GroupUser;                   // Grupo de Grupos del usuario Conectado.
     use GroupSpecial;                // Grupo de Entidades especiales del Grupo.
     use General;                     // Información sin argupación específica
     use Product;                     // Grupo de funciones sobre Productos
     use User;                        // Grupo de funciones sobre Usuarios
}