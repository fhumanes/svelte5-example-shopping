<?php
    
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;

trait Session {
    /*
     * Crear una nueva sesión y borrado de las caducadas
     */
    public function createSession(Request $request, Response $response)   // , array $args)
     {   
        global $errorMessages; 
        $param = $request->getParsedBody();          // Obtener los datos del JSON
         $login = $param['login'];
         $password = md5($param['password']);

         // Check User and password
         $stmt = $this->db->prepare("SELECT * FROM user WHERE login = :login AND password = :password AND active = 1");
         // Asignar el valor del parámetro
         $stmt->bindParam(':login', $login);
         $stmt->bindParam(':password', $password);
         $stmt->execute();
         $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

         if (count($data) <> 1) { // no se ha encontrado un usuario (ninguno o más de uno)
             $responseBody["error"] = true;
             $responseBody["message_num"] = '004';
             $responseBody["message"] = $errorMessages['004'];
             $responseBody["error_status"] = 401; 
             return $responseBody;
         }
         // Delete field "password"
         unset($data[0]['password']);

         $userPriv = array();
         $userPriv['dataUser']=$data[0]; // Save info of User identified
         $uuid = guidv4();
         // Save session in Data Base
         try {
             $stmt = $this->db->prepare("INSERT into shopping_session (uuid, creation_date, expiration_date, session_variables)
               VALUES (:uuid, :creation, :expiration, :variables)");
             
             $creation = date('Y-m-d H:i:s');
             $expiration = date('Y-m-d H:i:s', (strtotime (TIME_OUT)));
             $variables = json_encode($userPriv,JSON_NUMERIC_CHECK);
             
             $stmt->bindParam(':uuid', $uuid);
             $stmt->bindParam(':creation', $creation);         // Fecha actual
             $stmt->bindParam(':expiration', $expiration);
             $stmt->bindParam(':variables', $variables);
             $stmt->execute();


         } catch (PDOException $e) {
                 $responseBody["error"] = true;
                 $responseBody["message_num"] = '999';
                 $responseBody["message"] = $e->getMessage();
                 $responseBody["error_status"] = 500;
                 return $responseBody;
         }
         // Salida con ejecución correcta
         $responseBody["error"] = false;
         $responseBody["message_num"] = '005';
         $responseBody["message"] = $errorMessages['005'];
         $responseBody["tokenUser"] = $uuid;
         $responseBody["error_status"] = 200;
         $responseBody['dataUser']=$data[0]; // Save info of User identified
         return $responseBody;
     }

     /**
     * Revisa si la consulta contiene un Header "Token" para validar y recuperar session
     */
    public function controlSession(Request $request, Response $response)
    {
        global $errorMessages;
             
         // Delete session expired
         $now = date('Y-m-d H:i:s');         // Fecha actual
         $data2 = array();
         $stmt = $this->db->prepare("SELECT uuid  FROM shopping_session WHERE expiration_date < :now");
         // Asignar el valor del parámetro
         $stmt->bindParam(':now', $now);
         // Ejecutar y obtener resultados
         $stmt->execute();
         $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

         // Mostrar resultados
         foreach ($data as $fila) {
             $this->deleteSession($request, $response, $fila); // Borra session
         }

        // Getting request headers
        $headers = $request->getHeaders();
        // Verifying Authorization Header
        if (isset($headers['token-user'])) {
            // get the api key
            $token = $headers['token-user'];
            $token = $token[0];

            // GET data the UUID
             $stmt = $this->db->prepare("SELECT * FROM shopping_session WHERE uuid = :token");
             // Asignar el valor del parámetro
             $stmt->bindParam(':token', $token);
             $stmt->execute();
             $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

             if (count($data) <> 1) { // no se ha encontrado una sesión  (ninguno o más de uno)
                 $responseBody["error"] = true;
                 $responseBody["message_num"] = '001';
                 $responseBody["message"] = $errorMessages['001'];
                 $responseBody["error_status"] = 401; 
                 return $responseBody;
             }
            // validating token
            $now = date('Y-m-d H:i:s'); 
            if ($data[0]['expiration_date'] < $now) { // Expiry

                // Expiry
                $responseBody["error"] = true;
                $responseBody["message_num"] = '006';
                $responseBody["message"] = $errorMessages['006'];
                $responseBody["error_status"] = 401;   
                return $responseBody;
            } else {
                // Procede utilizar el recurso o metodo del llamado

                // Update session in Data Base
                $expiration = date('Y-m-d H:i:s', (strtotime (TIME_OUT)));
                 $stmt = $this->db->prepare("UPDATE shopping_session set expiration_date = :expiration WHERE id_shopping_session = :id");
                 $stmt->bindParam(':expiration',  $expiration);
                 $stmt->bindParam(':id', $data[0]["id_shopping_session"]);         // Id
                 $stmt->execute();

                 // Salida con ejecución correcta
                 $responseBody["error"] = false;
                 $responseBody["message_num"] = '005';
                 $responseBody["message"] = $errorMessages['005'];
                 $responseBody["token-user"] = $token;
                 $responseBody["error_status"] = 200;
                 return $responseBody;
            }
        } else {
            // api key is missing in header
            $responseBody["error"] = true;
            $responseBody["message_num"] = '007';
            $responseBody["message"] = $errorMessages['007'];
            $responseBody["error_status"] = 400;
            return $responseBody;
        }
    }

     /*
      * Logout session
      */
     public function deleteSession(Request $request, Response $response, $param)
     {   
         global $errorMessages;
         $token = $param['uuid'];
         // GET data the UUID
         $stmt = $this->db->prepare("SELECT * FROM shopping_session WHERE uuid = :token");
         // Asignar el valor del parámetro
         $stmt->bindParam(':token', $token);
         $stmt->execute();
         $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

         if (count($data) <> 1) { // no se ha encontrado una sesión  (ninguno o más de uno)
             $responseBody["error"] = true;
             $responseBody["message_num"] = '001';
             $responseBody["message"] = $errorMessages['001'];
             $responseBody["error_status"] = 401; 
             return $responseBody;
         }
         // delete session
         $stmt = $this->db->prepare("DELETE FROM  shopping_session WHERE uuid = :token");
         $stmt->bindParam(':token', $token);
         $stmt->execute();
         $responseBody["error"] = false;
         $responseBody["message_num"] = '008';
         $responseBody["message"] = $errorMessages['008'];
         $responseBody["error_status"] = 200; 
         return $responseBody;
     }
 /*
 * Recupera los datos de sesión (Usuarios, ...) del UUID facilitado
 */
    public function openSession($uuid)
    {
        global $errorMessages;
        
        $responseBody = array();
        $token = $uuid;
        $stmt = $this->db->prepare("SELECT * FROM shopping_session WHERE uuid = :token");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

         if (count($data) <> 1) { // no se ha encontrado una sesión  (ninguno o más de uno)
            // Token is not present in control session table
            $responseBody["error"] = true;
            $responseBody["message_num"] = '006';
            $responseBody["message"] = $errorMessages['006'];
            $responseBody["error_status"] = 401;
            return $responseBody;
         }
        return json_decode($data[0]['session_variables'], true) ;
    }
 /*
 * Salva los datos de sesión (Usuarios, ...) del UUID facilitado
 */
    public function saveSession($uuid,$newVariables)
    {
        global $errorMessages;
        
        $responseBody = array();
        $token = $uuid;
        $stmt = $this->db->prepare("SELECT * FROM shoppiong_session WHERE uuid = :token");
        $stmt->bindParam(':token', $token);
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

         if (count($data) <> 1) { // no se ha encontrado una sesión  (ninguno o más de uno)
            // Token is not present in control session table
            $responseBody["error"] = true;
            $responseBody["message_num"] = '006';
            $responseBody["message"] = $errorMessages['006'];
            $responseBody["error_status"] = 401;
            return $responseBody;
         }
        // Update the record with id=50 in the 'Cars' table
        $id = $data[0]['id_shopping_session'];

        $stmt = $this->db->prepare("UPDATE shopping_session SET session_variables = :variables WHERE id_shopping_session = :id");
        $stmt->execute([':variables'=> $newVariables,':id'=>$id]);
        
         $responseBody["error"] =false;
        return $responseBody;
        }    
    
    
    }

