<?php

/*********************** USEFULL FUNCTIONS **************************************/

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Selective\BasePath\BasePathMiddleware;
use Slim\Factory\AppFactory;


/**
 * Verificando los parametros requeridos en el método
 */
function verifyRequiredParams($required_fields, $request_params
        // ,  Request  $request, Response $response
        )
{
    global $errorMessages;
    $error = false;
    $error_fields = "";

    // $request_params = $request->getParsedBody();

    foreach ($required_fields as $field) {
        // if (!isset($request_params[$field]) || strlen(trim($request_params[$field])) <= 0) {
        if (!isset($request_params[$field])) {
            $error = true;
            $error_fields .= $field . ', ';
        }
    }

    if ($error) {
        // Required field(s) are missing or empty
        // echo error json and stop the app
        $responseBody = array();
        $responseBody["error"] = true;
        $responseBody["message_num"] = '003';
        $responseBody["message"] = str_replace("{1}",substr($error_fields, 0, -2),$errorMessages['003']);
        $responseBody["error_status"] = 400; 
        return $responseBody;
    }
    $responseBody = array();
    $responseBody["error"] = false;
    return $responseBody;
}

/**
 * Revisa si la consulta contiene un Header "Authorization" para validar
 */
function authenticate(Request $request, Response $response)
{
    global $errorMessages;
    // Getting request headers
    $headers = $request->getHeaders();
    // Verifying Authorization || authorization Header
    if (isset($headers['Authorization'])|| isset($headers['authorization']) ) {
        // get the api key
        if (isset($headers['Authorization']) ) $token = $headers['Authorization'];
        if (isset($headers['authorization']) ) $token = $headers['authorization'];
        
        // validating api key
        if (!($token[0] == API_KEY)) { //API_KEY declarada en Config.php

            // api key is not present in users table
            $responseBody["error"] = true;
            $responseBody["message_num"] = '002';
            $responseBody["message"] = $errorMessages['002'];
            $responseBody["error_status"] = 401; 
            return $responseBody;
        } else {
            //procede utilizar el recurso o metodo del llamado
            $responseBody = array();
            $responseBody["error"] = false;
            return $responseBody;
        }
    } else {
        // api key is missing in header
        $responseBody["error"] = true;
        $responseBody["message_num"] = '001';
        $responseBody["message"] = $errorMessages['001'];
        $responseBody["error_status"] = 400; 
        return $responseBody;
    }
}

// Generate 16 bytes (128 bits) of random data or use the data passed into the function.
   function guidv4($data = null) {
        
        $data = $data ?? random_bytes(16);
        assert(strlen($data) == 16);
    
        // Set version to 0100
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        // Set bits 6-7 to 10
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
    
        // Output the 36 character UUID.
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    } 

    // To normalize filer name
    function RemoveSpecialCharFile($str) // To normalize filer name
{
    $res = preg_replace('([^A-Za-z0-9_. ])', ' ', $str);
		$res = str_replace(' ','_',$res);
    return $res;
}

// Validación de estructuras de los array asociativos
function validarFormatoArray(array $datos=[['email'=>'','nombre'=>'']], array $clavesEsperadas = ['email','nombre']) {
    // Definimos las claves exactas que esperamos
    $valido = true;
    $registroInvalido = null;
    
    foreach ($datos as $indice => $registro) {
        // 1. Comprobar que es un array
        if (!is_array($registro)) {
            $valido = false;
            $registroInvalido = $indice;
            break;
        }

        // 2. Comprobar el número exacto de campos
        if (count($registro) !== 2) {
            $valido = false;
            $registroInvalido = $indice;
            break;
        }

        // 3. Comprobar que las claves son exactamente las esperadas
        $clavesActuales = array_keys($registro);
        
        // array_diff() nos dice si hay claves que faltan o sobran
        if (count(array_diff($clavesEsperadas, $clavesActuales)) > 0 || count(array_diff($clavesActuales, $clavesEsperadas)) > 0) {
            $valido = false;
            $registroInvalido = $indice;
            break;
        }
    }
    if ($valido) {
        // echo "✅ El formato de todos los registros es correcto.\n";
        return true;
    } else {
        // echo "❌ Error de formato en el registro con índice **$registroInvalido**.\n";
        return false;
    }
    
}
// Función para añadir trazas de depuración del código PHP
function custom_error($number, $text) {
    global $debugCode;

    if ($debugCode === true) {

        $logFile = __DIR__ . '/../error.log';

        // Si no existe, lo crea vacío
        if (!file_exists($logFile)) {
            // touch() crea el archivo y respeta permisos del sistema
            touch($logFile);
        }

        // Abrir en modo append (crea si no existe, pero ya lo controlamos arriba)
        $ddf = fopen($logFile, 'a');

        fwrite($ddf, "[" . date("r") . "] Error $number: $text\r\n");
        fclose($ddf);
    }
}



