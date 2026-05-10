# svelte5-example-shopping

###### Desarrollo COMPLETO Svelte5 Aplicación Shopping

![](assets/2026-05-10-20-18-00-image.png)

Es una aplicación completa, Front-End en **Svelte 5** y Back-End en **PHP SLIM 4**. 

He mejorado en mi codificación, en ambas plataformas, y el resultado es más legible, mantenible  y sencillo.

La APP es la misma que hice en [PHPRunner](https://fhumanes.com/blog/otros-ejemplos/crear-una-app-web/), después la hice en [REACT](https://fhumanes.com/blog/react/tutorial-de-react-comparacion-con-phprunner/) y ahora está hecha en Svelte 5.  
Me gusta utilizar este ejemplo por:

- Está orientada a que funcione en el Móvil y en el PC.
- Tiene gestión de usuarios y de roles.
- Maneja ficheros (imágenes), texto, password, números, fechas, check, etc. Gestiona GRID y FORMS.
- Controla las sesiones en el server y estas sesiones tienen un tiempo de caducidad, por no utilización de la aplicación.
- El modelo de datos es sencillo.
- Y la gestión de la aplicación, que no es relevante en el ejercicio, es muy simple y facilita la compresión rápida para su uso.

En el ejemplo he utilizado las guías anteriores de Svelte 5, salvo la
 de multi-idioma, porque no quise que tuviera más complejidad y así 
facilitar su entendimiento.

Como se ha indicado en las Guías, he utilizado los componentes de **[SVAR](https://svar.dev/)**.
 Son un poco complejos de entender, pero una vez que entiendes cómo 
funcionan, son muy potentes y sencillos de utilizar. He intentado que 
todo el desarrollo sea con los componentes, totalmente Free, de SVAR, 
que son un equipo estupendo, que facilitan buen soporte y que son de la 
U.E., en concreto de Polonia.

### Objetivo

Disponer de un ejemplo completo en Svelte 5 y PHP SLIM, con la 
utilización de las guías publicadas, que sirva como base para las 
personas que deseen incorporarse al desarrollo de aplicaciones en Svelte5.

**DEMO**:  https://fhumanes.com/my-shopping

Están los usuarios **admin/admin** y **usuario/usuario**.
Ruego que no destruyáis los ejemplos para que otros puedan utilizarlos y os propongo que os registréis para tener un «ambiente» particular para  vosotros. En el email, podéis utilizar el vuestro o poner uno ficticio. 

Si es el vuestro recibiréis un email, para que veáis que se puede hacer 
«TODO», desde la parte del server.

### Solución Técnica

Los productos, todos ellos Free, que he utilizado son:

- [**MySQL WorkBench**](https://www.mysql.com/products/workbench/)– Para hacer el **diseño de datos** y para **gestionar la información** en la Base de datos.
- [**NetBean for PHP**](https://netbeans.apache.org/tutorial/main/kb/docs/php/quickstart/). Para hacer el **desarrollo del Back-End.**
- [**Microsoft Visual Studio Code**](https://code.visualstudio.com/). Para hacer el **desarrollo del Front-End**.

Se puede utiliza el IDE de Microsoft para todo el desarrollo, pero estos son mis gustos y así los he utilizado.

En el IDE de Microsoft se integra **Copilot**, su **IA**. Ahora, es de gran ayuda, te soluciona muchos problemas y te genera  bastante código. No obstante, hay que conocer el entorno, pues aunque ha mejorado mucho, se sigue equivocando y tienes que «corregirlo».

Empecé el desarrollo en el orden que he indicado los productos.  Primero hice el modelo de datos y después hice el desarrollo del  Back-End, en SLIM PHP. Cuando tenía estas 2 partes, ya inicie el  desarrollo. Esto no significa que no haya habido correcciones o ajustes  (no muchos porque ya había hecho el desarrollo con otros productos). No  ha habido muchas correcciones, pero el que existan correcciones no es 
ningún problema, siempre que no tengas que cambiar la arquitectura  técnica de la solución.

### Modelo de datos

<img src="assets/2026-05-10-20-26-24-image.png" title="" alt="" data-align="center">

En «shopping_session» se mantiene los datos del usuario y los específicos de la sesión, por lo que esta arquitectura, además de segura y altamente potente, se podrían poner múltiples máquinas para gestionar la aplicación del Back-End. Teniendo en cuenta que la aplicación de  Front-End es Javascript y utiliza exclusivamente los recursos de los 
usuarios conectados, a nivel de recursos necesarios para la puesta en Producción es muy baja, lo que define una arquitectura muy escalabre y  pocos recursos necesarios.

He utilizado intensamente la integridad referencial y la normalización de los datos. Yo lo recomiendo como os expliqué en el [tutorial](https://fhumanes.com/blog/otros-ejemplos/tutorial-curso-basico-de-phprunner/) de PHPRunner. Creo que al final, casi todo son ventajas.

## Aplicación de Back-End

Como os he indicado, está desarrollada en PHP y el micro Framework de SLIM 4. Es bastante sencillo de construcción, en este caso, sin unirse a  PHPRunner y lo mejor, es que funciona en cualquier hosting barato, lo  que permite ponerlo en Producción con unos costes mínimos.

He mantenido la estructura general que he utilizado hasta ahora, pero he mejorado en la estructura del código, lo que hace que sea más  sencillo de entender y, desde mi punto de vista, construirlo. Os voy a  mostrar un conjunto de ficheros para que veáis la sencillez.

<details>
<summary>include/Config.php</summary>

```php
<?php

// URL de ejecución de la aplicación
define('SCRIPTS_DIR', '/shopping-server/v1'); 

// Tiempo de sesión
define('TIME_OUT','+30 minutes');

$server = 'test';  // 'test' or 'production'
// Configuración del entorno de Desarrollo "test" o de Producción
if ($server == 'test' ) {
// Path root of Directory files in File System or Disc
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASSWORD', 'XXXXXXXXX'); 
define('DB_NAME', 'shopping');
// Config PHPMailer
define('EMAIL_HOST', 'smtp.hostinger.com');
define('EMAIL_USER', 'info@fhumanes.com');
define('EMAIL_PASSWORD', 'XXXXXXXXX');
define('EMAIL_PORT', 465);

} else {
// In Server Linux
define('DB_HOST', 'localhost');
define('DB_USER', 'u637977917_admin' );
define('DB_PASSWORD', 'XXXXXXXXXXXXXX'); 
define('DB_NAME', 'u637977917_shopping'); 
// Config PHPMailer
define('EMAIL_HOST', 'smtp.hostinger.com');
define('EMAIL_USER', 'info@fhumanes.com');
define('EMAIL_PASSWORD', 'XXXXXXXXXX');
define('EMAIL_PORT', 465);
} 

//referencia generado con MD5(uniqueid(<some_string>, true))
define('API_KEY','61437cfc-caa5-4cf9-9bee-85fe47efb09a');

/**
* API Response HTTP CODE
* Used as reference for API REST Response Header
*
* 
* 
// Error messages to facilitate their translation
$errorMessages = array(
    "001" => "Falta Token de Autorización o ha expirado",
    "002" => "El Token de autorización es incorrecto",
    "003" => "Campo(s) Requerido(s) o atributo(s) {1} faltan o están vacíos",
    "004" => "Usuario o Password no válida",
    "005" => "Usuario identificado correctamente",
    "006" => "Sesión ha expirado",
    "007" => "Falta Sesión de usuario",
    "008" => "Sesión borrada correctamente",
    "009" => "Ya existe usuario con el mismo Login o Email",
    "010" => "Usuario dado de alta",
    "011" => "Grupo creado correctamente",
    "012" => "El nombre del Grupo está duplicado",
    "013" => "El Grupo ha sido actualizado",
    "014" => "No se ha actualizado el Grupo",
    "015" => "Grupo no encontrado o el usuario no es Administrador",
    "016" => "Grupo eliminado correctamente",
    "017" => "Usuario no está autorizado para hacer esta acción",
    "018" => "Usuario no existe o ya está en el Grupo",
    "019" => "Usuario ha sido añadido al Grupo",
    "020" => "Usuario asignado al grupo correctamente",
    "021" => "El Usuario ya existe en ese Grupo",
    "022" => "Usuario eliminado del grupo correctamente",
    "023" => "Sección creada correctamente",
    "024" => "La sección está duplicada",
    "025" => "Sección se ha actualizado correctamente",
    "026" => "Sección borrada correctamente",
    "027" => "No se ha podido borar la Sección",
    "028" => "Unidad de Medida creada correctamente",
    "029" => "La Unidad de Medida ya existe",
    "030" => "Unidad de Medida actualizada correctamente",
    "031" => "Ningún registro se ha modificado",
    "032" => "Unidad de Medida borrada correctamente",
    "033" => "Sección de la plantilla creada correctamente",
    "034" => "La Sección de la plantilla ya existe",
    "035" => "Sección de la plantilla actualizada correctamente",
    "036" => "Sección de la plantilla borrada correctamente",
    "037" => "Producto creado en el grupo correctamente",
    "038" => "Producto ya existe en el grupo",
    "039" => "Producto actualizado correctamente",
    "040" => "El Estado informado es incorrecto",
    "041" => "Producto se ha borrado correctamente",
    "042" => "No existe ningún usuario para este email",
    "043" => "Usuario creado correctamente",
    "044" => "Usuario no creado, login o email ya existen",
    "045" => "Usuario modificado correctamente",
    "046" => "Usuario borrado correctamente",
    "047" => "La antigua Password no corresponde o las nuevas Password son diferentes",
    "048" => "",
    "049" => "",
);
```

</details>
<details>
<summary>v1/index.php</summary>

```php
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


$app->run();
```

</details>
<details>
<summary>include/DbFunctions.php</summary>

```php
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
```

</details>
<details>
<summary>include/DBF_Identification.php</summary>

```php
<?php

    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;

    use PHPMailer\PHPMailer\PHPMailer;   // phpmAILER
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

trait Identification {

    /*
     * ADD nuevo usuario desde registro
     */
    public function registerUser(Request $request, Response $response )
    {
        global $errorMessages;
        // Verificación Token de Authorization
        $verify = authenticate($request, $response); 
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }

        $param = $request->getParsedBody();          // Obtener los datos del JSON
        // Verificación de que están todos los campos
        $verify = verifyRequiredParams(array('login', 'email', 'nombre', 'password'), $param);
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        $email = $param['email'];
        $login = $param['login'];
        $stmt = $this->db->prepare("SELECT * FROM user WHERE email = :email or login = :login ");
        if (!$stmt->execute([':email' => $email ,':login' => $login])) {
            $error = $stmt->errorInfo();
            $response->getBody()->write(json_encode(["message"=> $error]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(500);
        }
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (is_array($data)) {
            // Ya existe otro usuario con ese mismo email
           $response->getBody()->write(json_encode(["message"=> $errorMessages['009']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(401);
        }

        $now = date('Y-m-d H:i:s');         // Fecha actual
        $stmt = $this->db->prepare("INSERT INTO user (login, name_surname, password, email, date_last_change, isAdministrator, active)
                VALUES (:login,:name,:password,:email,'$now','0','1') ");
        $stmt->execute([':login' => $param['login'] ,':name' => $param['nombre'],
                        ':password'=>md5($param['password']),':email'=>$param['email']]);


        $this->sendEmail('info@fhumanes.com', [['email'=>$email,'name'=>$param['nombre']]], 
                [['email'=>'info@fhumanes.com','name'=>'Info']] ,
                'Solicitada el alta en la APP Shopping',
                '<p> Se ha solicitado y dado de alta un usuario con este email en la APP Shopping </p>'); // Mensaje de email de prueba

        $response->getBody()->write(json_encode(["message"=> $errorMessages['010']]));
         return $response
             ->withHeader('content-type', 'application/json')
             ->withStatus(200);
    }
    /*
    * Login del usuario
    */
    public function login(Request $request, Response $response )
    {
        global $errorMessages;
        // Verificación Token de Authorization
        $verify = authenticate($request, $response); 
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }

        $param = $request->getParsedBody();          // Obtener los datos del JSON
        // Verificación de que están todos los campos
        $verify = verifyRequiredParams(array('login', 'password'), $param);
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }

        $verify = $this->createSession($request, $response);    // Crea la sesión

        $error_status = $verify['error_status'];
        unset($verify['error_status']);             // se borraran las variables que no se desean enviar
        unset($verify['message_num']); 
        unset($verify['error']);
        $response->getBody()->write(json_encode($verify));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($error_status);   
        }

    /*
    * Logout del usuario
    */
    public function logout(Request $request, Response $response )
    {
        global $errorMessages;

        // Verificación Token de Usuario
        $verify = $this->controlSession($request, $response); 
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }

        $param = array();
        $param['uuid'] = $verify['token-user']; 
        $verify = $this->deleteSession($request,$response, $param);         // Eliminación de la sessión

        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        $response->getBody()->write(json_encode(["message"=> $errorMessages['008']]));  // OK, Se ha hecho Logout
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(200); 
        }

    // Acceso a la información de un Usuario
    public function userInfo(Request $request, Response $response, $args)   
    {   
        global $errorMessages;
        // Verificación Token de Usuario
        $verify = $this->controlSession($request, $response); 
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        $uuid = $verify['token-user']; 
        $session = $this->openSession($uuid);           // Recuperación datos usuario conectado
        $dataUser = $session['dataUser'];

        $id_user = $dataUser['id_user'];
        $is_administrator = $dataUser['isAdministrator'];

        // Obtener los datos del Usuario conectado
        $stmt = $this->db->prepare("SELECT u1.id_user, u1.login, ".
        " u1.name_surname, u1.email, u1.user_last_change, u2.name_surname user_last_change_text, ".
  " u1.date_last_change, u1.isAdministrator, u1.active ".
        " FROM user u1 ".
        " LEFT JOIN user u2 on ( u1.user_last_change = u2.id_user ) ".
        " WHERE u1.id_user =:id_user");
        // Parámetrtos
        $stmt->bindParam(':id_user', $id_user);     
        // Ejecutar y obtener resultados
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros

        $response->getBody()->write(json_encode(['data'=>$data]));
         return $response
             ->withHeader('content-type', 'application/json')
             ->withStatus(200);

    }
    // EDIT de Usuarios
    public function userUpdate(Request $request, Response $response, $args)   
    {   
        global $errorMessages;
        // Verificación Token de Usuario
        $verify = $this->controlSession($request, $response); 
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        $uuid = $verify['token-user']; 
        $session = $this->openSession($uuid);           // Recuperación datos usuario conectado
        $dataUser = $session['dataUser'];

        $id_user = $dataUser['id_user'];
        $is_administrator = $dataUser['isAdministrator'];

        // Verificación de que están todos los campos 
        // " login, name_surname,  email"
        // Todos los campos son obligatorios

        $param = $request->getParsedBody();          // Obtener los datos del JSON

        $verify = verifyRequiredParams(array('login', 'name_surname', 'email'), $param); 
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        try {
            // UPDATE USER
            $now = date('Y-m-d H:i:s');         // Fecha actual
            // $password = md5($param['password']);

            $stmt = $this->db->prepare("UPDATE user SET ".
                    " login = :login, ".
                    " name_surname = :name_surname, ".
                    " email = :email,".
                    " user_last_change = :id_user,".
                    " date_last_change = :last_change ".
            "WHERE id_user = :id ");

            // Asignar el valor del parámetro
            $stmt->bindParam(':login', $param['login']);
            $stmt->bindParam(':name_surname', $param['name_surname']);
            $stmt->bindParam(':email', $param['email']);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->bindParam(':last_change', $now);
            $stmt->bindParam(':id', $id_user);

            // Ejecutar y obtener resultados
            $stmt->execute();

            if ( $stmt->rowCount() == 0 ) { // No se ha actualizado ningún registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['031']]));
                return $response
                     ->withHeader('content-type', 'application/json')
                     ->withStatus(400);
            }

            $response->getBody()->write(json_encode(['message'=>$errorMessages['045']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(200);
         } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['message'=>$errorMessages['044']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }
    }

        // EDIT de Usuarios
    public function userPassword(Request $request, Response $response, $args)   
    {   
        global $errorMessages;
        // Verificación Token de Usuario
        $verify = $this->controlSession($request, $response); 
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        $uuid = $verify['token-user']; 
        $session = $this->openSession($uuid);           // Recuperación datos usuario conectado
        $dataUser = $session['dataUser'];

        $id_user = $dataUser['id_user'];


        // Verificación de que están todos los campos 
        // " login, name_surname,  email"
        // Todos los campos son obligatorios

        $param = $request->getParsedBody();          // Obtener los datos del JSON

        $verify = verifyRequiredParams(array('passwordOld', 'passwordNew1', 'passwordNew2'), $param); 
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }

        // Obtener los datos del ususario conectado"
        $stmt = $this->db->prepare("SELECT u1.id_user, u1.login, u1.password, ".
        " u1.name_surname, u1.email, u1.user_last_change, ".
  " u1.date_last_change, u1.isAdministrator, u1.active ".
        " FROM user u1 ".
        " WHERE u1.id_user =:id_user");
        // Parámetrtos
        $stmt->bindParam(':id_user', $id_user);     
        // Ejecutar y obtener resultados
        $stmt->execute();
        $userData = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
        $userData = $userData[0]; // Recogemos el primer registro
        custom_error(400, "Registro recuperado: ".print_r($userData, true));
        $ConexPassword = $userData['password'];
        $passwordOldMd5 = MD5($param['passwordOld']);

        // Controles de verificación de Password
        if ( $ConexPassword <> $passwordOldMd5 OR $param['passwordNew1'] <> $param['passwordNew2'] ) {
                $response->getBody()->write(json_encode(['message'=>$errorMessages['047']]));
                return $response
                     ->withHeader('content-type', 'application/json')
                     ->withStatus(400);
            }

        try {
            // UPDATE USER
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $password = md5($param['passwordNew1']);

            $stmt = $this->db->prepare("UPDATE user SET ".
                    " password = :password, ".
                    " user_last_change = :id_user,".
                    " date_last_change = :last_change ".
            "WHERE id_user = :id ");

            // Asignar el valor del parámetro
            $stmt->bindParam(':password', $password);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->bindParam(':last_change', $now);
            $stmt->bindParam(':id', $id_user);

            // Ejecutar y obtener resultados
            $stmt->execute();

            if ( $stmt->rowCount() == 0 ) { // No se ha actualizado ningún registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['031']]));
                return $response
                     ->withHeader('content-type', 'application/json')
                     ->withStatus(400);
            }

            $response->getBody()->write(json_encode(['message'=>$errorMessages['045']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(200);
         } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['message'=>$e->getMessage()]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }
    }


    private function sendEmail($from, $addressArray, $replyToArray , $subject, $body, $attachmentArray=array() )
        {
        // Validaciones de los parámetros de la función
        $validation = array();
        $validation[0] = validarFormatoArray($addressArray, ['email','name']);
        $validation[1] = validarFormatoArray($replyToArray, ['email','name']);
        $validation[2] = validarFormatoArray($attachmentArray, ['file','name']);
        if ($validation[0] == false || $validation[1] == false || $validation[1] == false )
        {
            return false;                   // Parámetros incorrectos
        }

        //Create an instance; passing `true` enables exceptions
        $mail = new PHPMailer(true);
        // $mail->SMTPDebug  = 2;      // En MODO DEBUG

        try {
            //Server settings
            // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
            $mail->isSMTP();                                            //Send using SMTP
            $mail->Host       = EMAIL_HOST;                             //Set the SMTP server to send through
            $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
            $mail->Username   = EMAIL_USER;                             //SMTP username
            $mail->Password   = EMAIL_PASSWORD;                         //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
            $mail->Port       = EMAIL_PORT;                             //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

            //Recipients
            $mail->setFrom($from);
            foreach ($addressArray as $indice => $registro) {
               $mail->addAddress($registro['email'], $registro['name']);     //Add a recipient 
            }
            $mail->addReplyTo($replyToArray[0]['email'],$replyToArray[0]['name']);
            // $mail->addCC('cc@example.com');
            // $mail->addBCC('bcc@example.com');

            //Attachments
            foreach ($attachmentArray as $indice => $registro) {
               $mail->addAttachment($registro['file'], $registro['name']);     //Add one file attachement
            }

            //Content
            $mail->isHTML(true);                                  //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body    = $body;
            // $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

            $mail->send();
            // echo 'Message has been sent';
            return true;
        } catch (Exception $e) {
            // echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            return false;
        }

    }
}
```

</details>

Utilizo el fichero «**Config.php**» para definir aquello que puede ser variable dependiendo del entorno. Este es el fichero que tenéis que ajustar para que funcione en vuestros PC’s.

Veis que el fichero «**index.php**«, está mucho más estructurado y sencillo de entender. También he incluido el [DEBUG](https://fhumanes.com/blog/guias-desarrollo/guia-34-metodo-basico-para-depuracion-codigo/) que os conté en el entorno de PHPRunner. En local, utilizo el debug integrado con NetBeans, pero en remoto, utilizo este otro sistema, muy 
parecido al «console.log» del entorno de JavaScript

En el fichero «**DbFunction.php**» es donde he incluido más cambios. He agrupado las funciones en diferentes ficheros y como está escrito con orientación a objeto, definiendo una clase para todas las funciones, he utilizado esta codificación para que quede más fácil de acceder a cada una de las funciones.

El fichero «**DBF_Identification.php**» he incluído las funciones de login, registro de nuevos usuarios, etc., todo lo referido a la identificación y a la gestión de la sesión (**token-user**, que es la identificación de la sesión).

Los que hayáis utilizado alguno de mis ejemplos anteriores, no tendréis problema en entender el código. Para cualquier de vosotros, podéis **escribirme un email**, para preguntarme lo que necesitéis.



## Aplicación Front-End

Como he indicado es Svelte 5 (sólo JavaScript, no TypeScript) y los componentes de SVAR UI. Entiendo que antes de acceder a este ejemplo, habéis visto, si no todos, la mayoría de los ejemplos previos o disponéis de conocimientos de Svelte 5. Si no es así, os va a ser difícil entender todos los códigos del ejemplo.

Según he ido desarrollando el ejemplo he observado que Copilot iba 
mejorando en su funcionamiento, estando en estos momentos en un nivel 
bastante bueno.

Igual que en el apartado anterior os mostraré algunos ficheros significativos, pero en este caso, como hay mucho más código, seguro que hay otros muchos que tienen «trucos» interesantes.

La aplicación es COMPLETA, es decir, es una aplicación básica pero que «toca» todos los aspectos de una aplicación «profesional», por ello, una vez que conoces lo básico del Svelte 5, este ejemplo puede mejorar tu aptitud para hacer algo de cierta calidad. 

Está orientada a que funcione en móvil y escritorio.

<details>
<summary>lib/config.js</summary>

```js

```
</details>
<details>
<summary>src/App.svelte</summary>

```js

```
</details>
<details>
<summary>component/Header_2.svelte</summary>

```js

```
</details>
<details>
<summary>lib/api.js</summary>

```js

```
</details>
<details>
<summary>lib/utils</summary>

```js

```
</details>
<details>
<summary>pages/MiPerfil.svelte</summary>

```js

```
</details>
<details>
<summary>pages/Grupos.svelte</summary>

```js

```
</details>
<details>
<summary>pages/Productos.svelte</summary>

```js

```
</details>
<details>
<summary>pages/ProductosForm.svelte</summary>

```js

```
</details>
