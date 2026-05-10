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
define('DB_PASSWORD', 'humanes'); 
define('DB_NAME', 'shopping');
// Config PHPMailer
define('EMAIL_HOST', 'smtp.hostinger.com');
define('EMAIL_USER', 'info@fhumanes.com');
define('EMAIL_PASSWORD', 'XXXXXXXXXXX');
define('EMAIL_PORT', 465);

} else {
// In Server Linux
define('DB_HOST', 'localhost');
define('DB_USER', 'u637977917_admin' );
define('DB_PASSWORD', 'adminFernando1#'); 
define('DB_NAME', 'u637977917_shopping'); 
// Config PHPMailer
define('EMAIL_HOST', 'smtp.hostinger.com');
define('EMAIL_USER', 'info@fhumanes.com');
define('EMAIL_PASSWORD', 'XXXXXXXXXXX');
define('EMAIL_PORT', 465);
} 

//referencia generado con MD5(uniqueid(<some_string>, true))
define('API_KEY','61437cfc-caa5-4cf9-9bee-85fe47efb09a');

/**
 * API Response HTTP CODE
 * Used as reference for API REST Response Header
 *
 */
/*
200 	OK
201 	Created
304 	Not Modified
400 	Bad Request
401 	Unauthorized
403 	Forbidden
404 	Not Found
409     Conflict
422 	Unprocessable Entity
500 	Internal Server Error
*/

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
    "048" => "El usuario no puede pedir este Informe",
    "049" => "Esta Lista no tiene ningún Producto pendiente de Comprar",
    "050" => "",
);

