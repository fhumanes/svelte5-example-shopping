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

#### Objetivo

Disponer de un ejemplo completo en Svelte 5 y PHP SLIM, con la 
utilización de las guías publicadas, que sirva como base para las 
personas que deseen incorporarse al desarrollo de aplicaciones en Svelte5.

**DEMO**:  https://fhumanes.com/my-shopping

Están los usuarios **admin/admin** y **usuario/usuario**.
Ruego que no destruyáis los ejemplos para que otros puedan utilizarlos y os propongo que os registréis para tener un «ambiente» particular para  vosotros. En el email, podéis utilizar el vuestro o poner uno ficticio. 

Si es el vuestro recibiréis un email, para que veáis que se puede hacer 
«TODO», desde la parte del server.

#### Solución Técnica

Los productos, todos ellos Free, que he utilizado son:

- [**MySQL WorkBench**](https://www.mysql.com/products/workbench/)– Para hacer el **diseño de datos** y para **gestionar la información** en la Base de datos.
- [**NetBean for PHP**](https://netbeans.apache.org/tutorial/main/kb/docs/php/quickstart/). Para hacer el **desarrollo del Back-End.**
- [**Microsoft Visual Studio Code**](https://code.visualstudio.com/). Para hacer el **desarrollo del Front-End**.

Se puede utiliza el IDE de Microsoft para todo el desarrollo, pero estos son mis gustos ![😊](https://s.w.org/images/core/emoji/17.0.2/svg/1f60a.svg) y así los he utilizado.

En el IDE de Microsoft se integra **Copilot**, su **IA**. Ahora, es de gran ayuda, te soluciona muchos problemas y te genera  bastante código. No obstante, hay que conocer el entorno, pues aunque ha mejorado mucho, se sigue equivocando y tienes que «corregirlo».

Empecé el desarrollo en el orden que he indicado los productos.  Primero hice el modelo de datos y después hice el desarrollo del  Back-End, en SLIM PHP. Cuando tenía estas 2 partes, ya inicie el  desarrollo. Esto no significa que no haya habido correcciones o ajustes  (no muchos porque ya había hecho el desarrollo con otros productos). No  ha habido muchas correcciones, pero el que existan correcciones no es 
ningún problema, siempre que no tengas que cambiar la arquitectura  técnica de la solución.

#### Modelo de datos

<img src="assets/2026-05-10-20-26-24-image.png" title="" alt="" data-align="center">

En «shopping_session» se mantiene los datos del usuario y los específicos de la sesión, por lo que esta arquitectura, además de segura y altamente potente, se podrían poner múltiples máquinas para gestionar la aplicación del Back-End. Teniendo en cuenta que la aplicación de  Front-End es Javascript y utiliza exclusivamente los recursos de los 
usuarios conectados, a nivel de recursos necesarios para la puesta en Producción es muy baja, lo que define una arquitectura muy escalabre y  pocos recursos necesarios.

He utilizado intensamente la integridad referencial y la normalización de los datos. Yo lo recomiendo como os expliqué en el [tutorial](https://fhumanes.com/blog/otros-ejemplos/tutorial-curso-basico-de-phprunner/) de PHPRunner. Creo que al final, casi todo son ventajas.

#### Aplicación de Back-End

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
