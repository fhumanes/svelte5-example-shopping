<?php
    
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;

trait GroupUser {
    /*
     * Listado de todos los Grupos del Usuario
     */
    public function ListGroupUser(Request $request, Response $response, $args)   
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
        $session = $this->openSession($uuid);   // Recuperación datos usuario conectado
        $dataUser = $session['dataUser'];
        
        // Obtener todos los grupos del usurio
        $stmt = $this->db->prepare("SELECT g.id_group, g.name, g.description, g.creation_date, g.user_administrator ".
        "FROM user_group u ".
        "JOIN `group` g ON (g.id_group = u.group_id ) ".
        "WHERE u.user_id = :id ".
        "ORDER BY g.name");
        // Asignar el valor del parámetro
        $stmt->bindParam(':id', $dataUser['id_user']);
        // Ejecutar y obtener resultados
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
        
        $response->getBody()->write(json_encode(['data'=>$data]));
         return $response
             ->withHeader('content-type', 'application/json')
             ->withStatus(200);
     }

    /*
     * Añadir Grupos del Usuario
    */
    public function AddGroupUser(Request $request, Response $response, $args)   
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
        $session = $this->openSession($uuid);   // Recuperación datos usuario conectado
        $dataUser = $session['dataUser'];
        
        // Verificación de que están todos los campos
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        $verify = verifyRequiredParams(array('name', 'description'), $param);
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        try {
            // INSERT INTO GROUP
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $stmt = $this->db->prepare("INSERT INTO `group` (name,description,creation_date,user_administrator ) "
                    . "VALUES (:name,:description,:creation,:administrator)");
            // Asignar el valor del parámetro
            $stmt->bindParam(':administrator', $dataUser['id_user']);
            $stmt->bindParam(':name', $param['name']);
            $stmt->bindParam(':description', $param['description']);
            $stmt->bindParam(':creation', $now);
            // Ejecutar y obtener resultados
            $stmt->execute();
            $lastId = $this->db->lastInsertId();  // id of Insert new record

            // INSERT INTO USER_GROUP
            $stmt = $this->db->prepare("INSERT INTO user_group (user_id,group_id ) "
                    . "VALUES (:user,:group)");
            // Asignar el valor del parámetro
            $stmt->bindParam(':user', $dataUser['id_user']);
            $stmt->bindParam(':group', $lastId);
            // Ejecutar y obtener resultados
            $stmt->execute();
            
            // INSERT INTO SECTION_GROUP
            $stmt = $this->db->prepare("INSERT INTO section_group (group_id, section ) "
                    . "SELECT $lastId, section FROM section_template ORDER BY id_section_template");

            // Ejecutar y obtener resultados
            $stmt->execute();

            $response->getBody()->write(json_encode(['message'=>$errorMessages['011']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(200);
         } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['message'=>$errorMessages['012']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }
     }
        
    /*
     * Editar/Actualizar Grupos del Usuario
    */
    public function EditGroupUser(Request $request, Response $response, $args)   
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
        $session = $this->openSession($uuid);   // Recuperación datos usuario conectado
        $dataUser = $session['dataUser'];
        
        $id = $args['id'];                      // Id del grupo a modificar
        
        // Verificación de que están todos los campos
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        $verify = verifyRequiredParams(array('name', 'description','creation_date','user_administrator'), $param);
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        try {
            // Update GROUP
            $stmt = $this->db->prepare("UPDATE `group` ". 
                    " SET name = :name, description = :description, ". 
                    " creation_date = :creation_date, user_administrator = :user_administrator "
                    . "WHERE id_group = :id AND user_administrator = :administrator ");
            // Asignar el valor del parámetro
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $param['name']);
            $stmt->bindParam(':description', $param['description']);
            $stmt->bindParam(':creation_date', $param['creation_date']);
            $stmt->bindParam(':user_administrator', $param['user_administrator']);
            $stmt->bindParam(':administrator', $dataUser['id_user']);
            // Ejecutar y obtener resultados
            $stmt->execute();
            // Saber cuántos registros fueron modificados
            $recordUpdated = $stmt->rowCount();                     // Obtener los registros actualizados

            if ( $recordUpdated > 0 ) {     // Se ha actualizado algun registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['013']]));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response->getBody()->write(json_encode(['message'=>$errorMessages['014']]));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(400);                
            }
         } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['message'=>$errorMessages['012']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }
        
     }
    /*
     * Borrar Grupos del Usuario
     */
    public function DeleteGroupUser(Request $request, Response $response, $args)   
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
        $session = $this->openSession($uuid);   // Recuperación datos usuario conectado
        $dataUser = $session['dataUser'];       
        $id = $args['id'];                      // Id del grupo a modificar
        
        // Obtener los datos del Grupo
        $stmt = $this->db->prepare("SELECT * FROM `group` WHERE id_group = :id ");
        $stmt->bindParam(':id',$id );
        // Ejecutar y obtener resultados
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
        if (count($data) <> 1 ) { // No se ha encontrado Grupo o se han encontrado varios
            $response->getBody()->write(json_encode(['message'=>$errorMessages['015']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400); 
        }
        if ($dataUser['id_user'] <> $data[0]['user_administrator'] ) { // El usuario conectado no es el administrador del Grupo
            $response->getBody()->write(json_encode(['message'=>$errorMessages['015']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400);             
        }
        try {
            // Delete GROUP
            $stmt = $this->db->prepare("DELETE  FROM `group` WHERE id_group = :id ");
            $stmt->bindParam(':id',$id );
            // Ejecutar y obtener resultados
            $stmt->execute();
            $response->getBody()->write(json_encode(['message'=>$errorMessages['016']]));
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
    /*
     * Listado de todos los Grupos para Administración del sistema
     */
    public function ListGroupAdmin(Request $request, Response $response, $args)   
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
        $session = $this->openSession($uuid);   // Recuperación datos usuario conectado
        $dataUser = $session['dataUser'];
        // ------------------------------------ Control de ususario Administtador ------------------
        $id_user = $dataUser['id_user'];
        $is_administrator = $dataUser['isAdministrator'];

 
        // El usuario conectado es Administrator?
        if ($is_administrator <> 1 ) {
            $response->getBody()->write(json_encode(["message"=> $errorMessages['017']])); 
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400); 
        }
        // ------------------------------------ Control de ususario Administtador ------------------
        // Obtener todos los grupos del usurio
        $stmt = $this->db->prepare("SELECT ".
            " id_group, name, description, creation_date, user_administrator, name_surname user_administrator_text ".
            " FROM `group` ".
            " LEFT JOIN user on (id_user = user_administrator)");
        // Ejecutar y obtener resultados
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
        
        $response->getBody()->write(json_encode(['data'=>$data]));
         return $response
             ->withHeader('content-type', 'application/json')
             ->withStatus(200);
     }

    /*
     * Añadir Grupos 
    */
    public function AddGroupAdmin(Request $request, Response $response, $args)   
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
        $session = $this->openSession($uuid);   // Recuperación datos usuario conectado
        $dataUser = $session['dataUser'];
        
        // ------------------------------------ Control de ususario Administtador ------------------
        $id_user = $dataUser['id_user'];
        $is_administrator = $dataUser['isAdministrator'];

 
        // El usuario conectado es Administrator?
        if ($is_administrator <> 1 ) {
            $response->getBody()->write(json_encode(["message"=> $errorMessages['017']])); 
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400); 
        }
        // ------------------------------------ Control de ususario Administtador ------------------
        // Verificación de que están todos los campos
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        $verify = verifyRequiredParams(array('name', 'description','creation_date','user_administrator'), $param);
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        
        try {
            // INSERT INTO GROUP
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $stmt = $this->db->prepare("INSERT INTO `group` (name,description,creation_date,user_administrator ) "
                    . "VALUES (:name,:description,:creation,:administrator)");
            // Asignar el valor del parámetro
            $stmt->bindParam(':administrator', $param['user_administrator']);
            $stmt->bindParam(':name', $param['name']);
            $stmt->bindParam(':description', $param['description']);
            $stmt->bindParam(':creation', $now);
            // Ejecutar y obtener resultados
            $stmt->execute();
            $lastId = $this->db->lastInsertId();  // id of Insert new record

            // INSERT INTO USER_GROUP
            $stmt = $this->db->prepare("INSERT INTO user_group (user_id,group_id ) "
                    . "VALUES (:user,:group)");
            // Asignar el valor del parámetro
            $stmt->bindParam(':user', $param['user_administrator']);
            $stmt->bindParam(':group', $lastId);
            // Ejecutar y obtener resultados
            $stmt->execute();
            
            // INSERT INTO SECTION_GROUP
            $stmt = $this->db->prepare("INSERT INTO section_group (group_id, section ) "
                    . "SELECT $lastId, section FROM section_template ORDER BY id_section_template");

            // Ejecutar y obtener resultados
            $stmt->execute();

            $response->getBody()->write(json_encode(['message'=>$errorMessages['011']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(200);
         } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['message'=>$errorMessages['012']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }
     }
        
    /*
     * Editar/Actualizar Grupos 
    */
    public function EditGroupAdmin(Request $request, Response $response, $args)   
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
        $session = $this->openSession($uuid);   // Recuperación datos usuario conectado
        $dataUser = $session['dataUser'];
        
        $id = $args['id'];                      // Id del grupo a modificar
        // ------------------------------------ Control de ususario Administtador ------------------
        $id_user = $dataUser['id_user'];
        $is_administrator = $dataUser['isAdministrator'];

 
        // El usuario conectado es Administrator?
        if ($is_administrator <> 1 ) {
            $response->getBody()->write(json_encode(["message"=> $errorMessages['017']])); 
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400); 
        }
        // ------------------------------------ Control de ususario Administtador ------------------
        
        // Verificación de que están todos los campos
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        $verify = verifyRequiredParams(array('name', 'description','creation_date','user_administrator'), $param);
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        try {
            // Update GROUP
            $stmt = $this->db->prepare("UPDATE `group` ". 
                    " SET name = :name, description = :description, ". 
                    " creation_date = :creation_date, user_administrator = :user_administrator "
                    . "WHERE id_group = :id  ");
            // Asignar el valor del parámetro
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':name', $param['name']);
            $stmt->bindParam(':description', $param['description']);
            $stmt->bindParam(':creation_date', $param['creation_date']);
            $stmt->bindParam(':user_administrator', $param['user_administrator']);
            // $stmt->bindParam(':administrator', $dataUser['id_user']);
            // Ejecutar y obtener resultados
            $stmt->execute();
            // Saber cuántos registros fueron modificados
            $recordUpdated = $stmt->rowCount();                     // Obtener los registros actualizados

            if ( $recordUpdated > 0 ) {     // Se ha actualizado algun registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['013']]));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(200);
            } else {
                $response->getBody()->write(json_encode(['message'=>$errorMessages['014']]));
                return $response
                    ->withHeader('content-type', 'application/json')
                    ->withStatus(400);                
            }
         } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['message'=>$errorMessages['012']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }
        
     }
    /*
     * Borrar Grupos 
     */
    public function DeleteGroupAdmin(Request $request, Response $response, $args)   
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
        $session = $this->openSession($uuid);   // Recuperación datos usuario conectado
        $dataUser = $session['dataUser'];       
        $id = $args['id'];                      // Id del grupo a modificar

        // ------------------------------------ Control de ususario Administtador ------------------
        $id_user = $dataUser['id_user'];
        $is_administrator = $dataUser['isAdministrator'];
 
        // El usuario conectado es Administrator?
        if ($is_administrator <> 1 ) {
            $response->getBody()->write(json_encode(["message"=> $errorMessages['017']])); 
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400); 
        }
        // ------------------------------------ Control de ususario Administtador ------------------
        
        try {
            // Delete GROUP
            $stmt = $this->db->prepare("DELETE  FROM `group` WHERE id_group = :id ");
            $stmt->bindParam(':id',$id );
            // Ejecutar y obtener resultados
            $stmt->execute();
            $response->getBody()->write(json_encode(['message'=>$errorMessages['016']]));
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
     
}
