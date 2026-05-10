<?php
    
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;

trait User {
    
    // Acceso a todos los Usuarios
    public function ListUser(Request $request, Response $response, $args)   
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

     
        // El usuario conectado es Administrator?
        if ($is_administrator <> 1 ) {
            $response->getBody()->write(json_encode(["message"=> $errorMessages['017']])); 
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400); 
        }
 
        // Obtener todos los Usuarios"
        $stmt = $this->db->prepare("SELECT id_user, login, name_surname, email, user_last_change, ".
                " date_last_change, isAdministrator, active FROM user ORDER BY login");
        // Ejecutar y obtener resultados
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
        
        $response->getBody()->write(json_encode(['data'=>$data]));
         return $response
             ->withHeader('content-type', 'application/json')
             ->withStatus(200);
    
    }
    
    // Acceso a  Usuario Por Email
    public function ListUserEmail(Request $request, Response $response, $args)   
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
        
        $email = $args['email'];                      // El Email a buscar

        // Obtener Usuario por email"
        $stmt = $this->db->prepare("SELECT id_user, name_surname FROM user WHERE email = :email");
        $stmt->bindParam(':email', $email);
        // Ejecutar y obtener resultados
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
        
        if ( $stmt->rowCount() == 0 ) { // No se ha leído ningún registro
            $response->getBody()->write(json_encode(['message'=>$errorMessages['042']]));
            return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
        }
        
        $response->getBody()->write(json_encode(['data'=>$data[0]]));
         return $response
             ->withHeader('content-type', 'application/json')
             ->withStatus(200);
    }
    
    // ADD de Usuarios
    public function AddUser(Request $request, Response $response, $args)   
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
        
        // El usuario conectado es Administrator?
        if ($is_administrator <> 1 ) {
            $response->getBody()->write(json_encode(["message"=> $errorMessages['017']])); 
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400); 
        }
         
        // Verificación de que están todos los campos 
        // "id_user, login, name_surname, password, email, isAdministrator, active"
        // Todos los campos son obligatorios
        
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        $verify = verifyRequiredParams(array('login', 'name_surname', 'password', 'email', 'isAdministrator', 'active'), $param); 
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        try {
            // INSERT INTO USER
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $password = md5($param['password']);

            $stmt = $this->db->prepare("INSERT INTO user ".
                    "(login, name_surname, password, email, isAdministrator, active , user_last_change, date_last_change) ".
            "VALUES (:login, :name_surname, :password, :email, :isAdministrator, :active, :id_user, :last_change) ");
 
            // Asignar el valor del parámetro
            $stmt->bindParam(':login', $param['login']);
            $stmt->bindParam(':name_surname', $param['name_surname']);
            $stmt->bindParam(':password',$password);
            $stmt->bindParam(':email', $param['email']);
            $stmt->bindParam(':isAdministrator', $param['isAdministrator']);
            $stmt->bindParam(':active', $param['active']);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->bindParam(':last_change', $now);

            // Ejecutar y obtener resultados
            $stmt->execute();
            $lastId = $this->db->lastInsertId();  // id of Insert new record

            $response->getBody()->write(json_encode(['message'=>$errorMessages['043']]));
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
    public function EditUser(Request $request, Response $response, $args)   
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
        
        $id = $args['id'];                       // Id del Usuario
        
        // El usuario conectado es Administrator?
        if ($is_administrator <> 1 ) {
            $response->getBody()->write(json_encode(["message"=> $errorMessages['017']])); 
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400); 
        }
         
        // Verificación de que están todos los campos 
        // "id_user, login, name_surname, password, email, isAdministrator, active"
        // Todos los campos son obligatorios
        
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        
        $existePassword = false;
        if ($param['password'] <> null) { $existePassword = true; } // Se controla si viene Password
       
        $verify = verifyRequiredParams(array('login', 'name_surname', 'email', 'isAdministrator', 'active'), $param); 
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
            
            if ($existePassword) {

            $stmt = $this->db->prepare("UPDATE user SET ".
                    " login = :login, ".
                    " name_surname = :name_surname, ".
                    " password = :password, ".
                    " email = :email,".
                    " isAdministrator = :isAdministrator,". 
                    " active = :active,".
                    " user_last_change = :id_user,".
                    " date_last_change = :last_change ".
            "WHERE id_user = :id ");
 
            // Asignar el valor del parámetro
            $stmt->bindParam(':login', $param['login']);
            $stmt->bindParam(':name_surname', $param['name_surname']);
            $stmt->bindParam(':password',$param['password']);
            $stmt->bindParam(':email', $param['email']);
            $stmt->bindParam(':isAdministrator', $param['isAdministrator']);
            $stmt->bindParam(':active', $param['active']);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->bindParam(':last_change', $now);
            $stmt->bindParam(':id', $id);
            } else {
            $stmt = $this->db->prepare("UPDATE user SET ".
                    " login = :login, ".
                    " name_surname = :name_surname, ".
 
                    " email = :email,".
                    " isAdministrator = :isAdministrator,". 
                    " active = :active,".
                    " user_last_change = :id_user,".
                    " date_last_change = :last_change ".
            "WHERE id_user = :id ");
 
            // Asignar el valor del parámetro
            $stmt->bindParam(':login', $param['login']);
            $stmt->bindParam(':name_surname', $param['name_surname']);

            $stmt->bindParam(':email', $param['email']);
            $stmt->bindParam(':isAdministrator', $param['isAdministrator']);
            $stmt->bindParam(':active', $param['active']);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->bindParam(':last_change', $now);
            $stmt->bindParam(':id', $id);    
            }

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
    
    // DELETE de Usuarios
    public function DeleteUser(Request $request, Response $response, $args)   
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
        
        $id = $args['id'];                       // Id del Usuario
        
        // El usuario conectado es Administrator?
        if ($is_administrator <> 1 ) {
            $response->getBody()->write(json_encode(["message"=> $errorMessages['017']])); 
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400); 
        }
         

        try {
            // DELETE  USER
            $now = date('Y-m-d H:i:s');         // Fecha actual
            
            // Tabla USER_GROUP
            $stmt = $this->db->prepare("DELETE FROM user_group ".
            " WHERE user_id = :id ");
            // Asignar el valor del parámetro
            $stmt->bindParam(':id', $id);
            // Ejecutar y obtener resultados
            $stmt->execute();
            
            // Tabla GROUP
            $stmt = $this->db->prepare("DELETE FROM `group` ".
            " WHERE user_administrator = :id ");
            // Asignar el valor del parámetro
            $stmt->bindParam(':id', $id);
            // Ejecutar y obtener resultados
            $stmt->execute();
            
            // Tabla USER
            $stmt = $this->db->prepare("DELETE FROM user ".
            " WHERE id_user = :id ");
            // Asignar el valor del parámetro
            $stmt->bindParam(':id', $id);
            // Ejecutar y obtener resultados
            $stmt->execute();
            
            if ( $stmt->rowCount() == 0 ) { // No se ha actualizado ningún registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['031']]));
                return $response
                     ->withHeader('content-type', 'application/json')
                     ->withStatus(400);
            }

            $response->getBody()->write(json_encode(['message'=>$errorMessages['046']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(200);
         } catch (PDOException $e) {
             custom_error(300, "Error en Delete USER: ".print_r($e, true));
            $response->getBody()->write(json_encode(['message'=>$e->getMessage()]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }
    }
    
}
