<?php
/**
 *
 * @About:      API Interface
 * @File:       index.php
 * @Date:       $Date:$ Sep 2025
 * @Version:    $Rev:$ 1.0
 * @Developer:  Federico Guzman || Modificado por Fernando Humanes para PHP 8.3
 **/

/* Los headers permiten acceso desde otro dominio (CORS) a nuestro REST API o desde un cliente remoto via HTTP
 * Removiendo las lineas header() limitamos el acceso a nuestro RESTfull API a el mismo dominio
 * Nótese los métodos permitidos en Access-Control-Allow-Methods. Esto nos permite limitar los métodos de consulta a nuestro RESTfull API
 * Mas información: https://developer.mozilla.org/en-US/docs/Web/HTTP/Access_control_CORS
 **/

// $dominioPermitido = "http://localhost:3000";

// header("Access-Control-Allow-Origin: $dominioPermitido"); // Para restringir desde dónde se pueden hacer peticines
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type, authorization, Authorization, token-user ");
// header("Access-Control-Allow-Headers: *");

header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: PUT, GET, POST, DELETE, OPTIONS');
// header("Access-Control-Allow-Headers: X-Requested-With");
header('Content-Type: text/html; charset=utf-8');
header('Content-Type: multipart/form-data');
header('Content-Type: application/x-www-form-urlencoded');
header('Content-Type: application/json');
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

// session_cache_limiter(false);

include_once '../include/Config.php';       // Configuration Rest Api
include_once '../include/Function.php';     // Funciones generales

// require_once("../../include/dbcommon.php"); // DataBase PHPRunner

// Debug
$debugCode = false; // On | Off, de depuración y volcado en el fichero "error.log"
custom_error(1,"URL ejecutada: ".$_SERVER["REQUEST_URI"]);          // To debug
custom_error(2,"Método de petición: ".$_SERVER['REQUEST_METHOD']);  // to Debug
$body = $body = file_get_contents('php://input');
custom_error(3,"Body: ".$body);                                    // to Debug
//  custom_error(4,"Campos POST: ".print_r($_POST,true));               // to Debug
// $debugCode = false;

// use App\Models\Db;  // Utilizamos la conexión de PHPRunner
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Factory\AppFactory;

use Slim\Middleware\ErrorMiddleware;
// use DI\Container;
use Slim\Routing\RouteCollectorProxy;
use Slim\Middleware\BodyParsingMiddleware;


require_once __DIR__ . '/../libs/autoload.php';   // Library SLIM v4

$app = AppFactory::create();

$app->addRoutingMiddleware();
// $app->add(new BasePathMiddleware($app)); // No usar si se ejecuta en subdirectorio
$app->addErrorMiddleware(true, true, true);
$app->addBodyParsingMiddleware();


$app->setBasePath(SCRIPTS_DIR);             // Indica el directorio desde donde está trabajando
// $app->setBasePath('/shopping-server/v1');             // Indica el directorio desde donde está trabajando

require_once '../include/DbFunctions.php';
$db = new DbFunctions();

// Necesario para las peticiones "OPTIONS"
$app->options('/{routes:.+}', function (Request $request, Response $response) {
    return $response;
});

$app->post('/userRegister', function (Request $request, Response $response, $args) use ($db) {         // Register User
         return $db->registerUser($request, $response, $args);
    });
$app->post('/login', function (Request $request, Response $response, $args) use ($db) {         // Login
         return $db->login($request, $response, $args);
    });
$app->post('/logout', function (Request $request, Response $response, $args) use ($db) {        // Login
         return $db->logout($request, $response, $args);
    });
    
$app->get('/userInfo', function (Request $request, Response $response, $args) use ($db) {         // Info user
         return $db->userInfo($request, $response, $args);
    });
$app->put('/userUpdate', function (Request $request, Response $response, $args) use ($db) {       // Update user
         return $db->userUpdate($request, $response, $args);
    });
$app->put('/userPassword', function (Request $request, Response $response, $args) use ($db) {      // Change Password
         return $db->userPassword($request, $response, $args);
    });
    
// Grupo de Grupos (para los grupos en los que pertenece el Usuario) Habrá otro grupo para el Administrador
$app->group('/group', function (RouteCollectorProxy $group) use ($db) {
    $group->get('', function (Request $request, Response $response, $args) use ($db) {          // List User
         return $db->ListGroupUser($request, $response, $args);
    });
    $group->post('', function (Request $request, Response $response, $args) use ($db) {          // Add User
         return $db->AddGroupUser($request, $response, $args);
    });
    $group->put('/{id}', function (Request $request, Response $response, $args) use ($db) {      // Update User
         return $db->EditGroupUser($request, $response, $args);
    });
    $group->delete('/{id}', function (Request $request, Response $response, $args) use ($db) {   // Delete User
         return $db->DeleteGroupUser($request, $response, $args);
    });
});

// Grupo de Grupos  para el Administrador
$app->group('/groupAdmin', function (RouteCollectorProxy $group) use ($db) {
    $group->get('', function (Request $request, Response $response, $args) use ($db) {          // List User
         return $db->ListGroupAdmin($request, $response, $args);
    });
    $group->post('', function (Request $request, Response $response, $args) use ($db) {          // Add User
         return $db->AddGroupAdmin($request, $response, $args);
    });
    $group->put('/{id}', function (Request $request, Response $response, $args) use ($db) {      // Update User
         return $db->EditGroupAdmin($request, $response, $args);
    });
    $group->delete('/{id}', function (Request $request, Response $response, $args) use ($db) {   // Delete User
         return $db->DeleteGroupAdmin($request, $response, $args);
    });
});

// Grupo de Grupos (para los grupos en los que pertenece el Usuario) 
$app->group('/userGroup', function (RouteCollectorProxy $group) use ($db) {
    $group->get('/{group}', function (Request $request, Response $response, $args) use ($db) {          // List Group
         return $db->ListUserGroup($request, $response, $args);
    });
    $group->post('/{group}', function (Request $request, Response $response, $args) use ($db) {          // Add Group
         return $db->AddUserGroup($request, $response, $args);
    });
    /*
     $group->put('/{group}/{id}', function (Request $request, Response $response, $args) use ($db) {      // Update Group
         return $db->EditUserGroup($request, $response, $args);
    });
     */
    $group->delete('/{group}/{id}', function (Request $request, Response $response, $args) use ($db) {   // Delete Group
         return $db->DeleteUserGroup($request, $response, $args);
    });
});
// Grupo de Grupos (para la gestión de Secciones del grupo)
$app->group('/secctionGroup', function (RouteCollectorProxy $group) use ($db) {
    $group->get('/{group}', function (Request $request, Response $response, $args) use ($db) {          // List User
         return $db->ListSectionGroup($request, $response, $args);
    });
    $group->post('/{group}', function (Request $request, Response $response, $args) use ($db) {          // Add User
         return $db->AddSectionGroup($request, $response, $args);
    });
    $group->put('/{group}/{id}', function (Request $request, Response $response, $args) use ($db) {      // Update User
         return $db->EditSectionGroup($request, $response, $args);
    });
    $group->delete('/{group}/{id}', function (Request $request, Response $response, $args) use ($db) {   // Delete User
         return $db->DeleteSectionGroup($request, $response, $args);
    });
});

// Unit_measure Gestión de Unidades de Medida (LIST pública, resto necesario ADMIN
$app->group('/measure', function (RouteCollectorProxy $group) use ($db) {
    $group->get('', function (Request $request, Response $response, $args) use ($db) {          // List User
         return $db->ListMeasure($request, $response, $args);
    });
    $group->post('', function (Request $request, Response $response, $args) use ($db) {          // Add User
         return $db->AddMeasure($request, $response, $args);
    });
    $group->put('/{id}', function (Request $request, Response $response, $args) use ($db) {      // Update User
         return $db->EditMeasure($request, $response, $args);
    });
    $group->delete('/{id}', function (Request $request, Response $response, $args) use ($db) {   // Delete User
         return $db->DeleteMeasure($request, $response, $args);
    });
});

// SECTION_TEMPLATE - Plantilla de Secciones
$app->group('/sectionTemplate', function (RouteCollectorProxy $group) use ($db) {
    $group->get('', function (Request $request, Response $response, $args) use ($db) {          // List User
         return $db->ListSectionTemplate($request, $response, $args);
    });
    $group->post('', function (Request $request, Response $response, $args) use ($db) {          // Add User
         return $db->AddSectionTemplate($request, $response, $args);
    });
    $group->put('/{id}', function (Request $request, Response $response, $args) use ($db) {      // Update User
         return $db->EditSectionTemplate($request, $response, $args);
    });
    $group->delete('/{id}', function (Request $request, Response $response, $args) use ($db) {   // Delete User
         return $db->DeleteSectionTemplate($request, $response, $args);
    });
});

// PRODUCT - Productos de un Grupo
$app->group('/product', function (RouteCollectorProxy $group) use ($db) {
    $group->get('/{group}', function (Request $request, Response $response, $args) use ($db) {          // List Product
         return $db->ListProduct($request, $response, $args);
    });
    $group->post('/{group}', function (Request $request, Response $response, $args) use ($db) {          // Add Product
         return $db->AddProduct($request, $response, $args);
    });
    $group->put('/{group}/{id}', function (Request $request, Response $response, $args) use ($db) {      // Update Product
         return $db->EditProduct($request, $response, $args);
    });
    $group->put('/{group}/{id}/{state}', function (Request $request, Response $response, $args) use ($db) {      // Update Product
         return $db->EditProductState($request, $response, $args);
    });
    $group->delete('/{group}/{id}', function (Request $request, Response $response, $args) use ($db) {   // Delete Product
         return $db->DeleteProduct($request, $response, $args);
    });
});

// USER - Usuarios de la App
$app->group('/user', function (RouteCollectorProxy $group) use ($db) {
    $group->get('', function (Request $request, Response $response, $args) use ($db) {          // List User
         return $db->ListUser($request, $response, $args);
    });
    $group->get('/{email}', function (Request $request, Response $response, $args) use ($db) {   // List User X Email
         return $db->ListUserEmail($request, $response, $args);
    });
    $group->post('', function (Request $request, Response $response, $args) use ($db) {          // Add User
         return $db->AddUser($request, $response, $args);
    });
    $group->put('/{id}', function (Request $request, Response $response, $args) use ($db) {          // Update User
         return $db->EditUser($request, $response, $args);
    });
    $group->delete('/{id}', function (Request $request, Response $response, $args) use ($db) {       // Delete User
         return $db->DeleteUser($request, $response, $args);
    });
});
// Inform en PDF
$app->get('/informe/{idgroup}', function (Request $request, Response $response, $args) use ($db) {   // Informe de lista de Compra
         return $db->jsonInforme001($request, $response, $args);
    });


$app->run();

