<?php
    
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;

trait GroupSpecial {

// Acceso a los Usuarios de un Grupo
    public function ListUserGroup(Request $request, Response $response, $args)   
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
        
        $id_group = $args['group'];                      // Id del grupo seleccionado
        $id_user = $dataUser['id_user'];
        // Obtener todos los usuarios del Grupo
        $stmt = $this->db->prepare("SELECT ug.id_user_group, ug.group_id, g.name, ug.user_id ,".        
            " IF((user_administrator = $id_user AND id_user <> $id_user )".
            " OR (id_user = $id_user AND user_administrator <> id_user) , '1', '0' ) isUpdatable, ". 
            " u.name_surname, u.email".
            " FROM user_group ug ". 
            " JOIN `group` g ON (g.id_group = ug.group_id) ".
            " JOIN user u ON ( u.id_user = ug.user_id ) " .
            " WHERE ug.group_id = :id_group".
            " ORDER BY u.name_surname");
        $stmt->bindParam(':id_group', $id_group);
        // Ejecutar y obtener resultados
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
        
        $response->getBody()->write(json_encode(['data'=>$data]));
         return $response
             ->withHeader('content-type', 'application/json')
             ->withStatus(200);
    
    }
    // Añadir un usuario a un Grupo
    public function AddUserGroup(Request $request, Response $response, $args)   
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
        
        $id_group = $args['group'];                      // Id del grupo seleccionado
        $id_user = $dataUser['id_user'];
        
        // Verificar que el ususario conectado es Administrador del grupo
        $stmt = $this->db->prepare("SELECT user_administrator FROM `group` WHERE id_group = :id_group".
                " AND user_administrator = $id_user");
        $stmt->bindParam(':id_group', $id_group);
        // Ejecutar y obtener resultados
        $stmt->execute();
        $dataGroup = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
     
        // El usuario conectado no es el admin del Grupo
        if ( count($dataGroup) <> 1 ) {
            $response->getBody()->write(json_encode(["message"=> $errorMessages['017']])); 
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400); 
        }
 
        // Verificación de que están todos los campos
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        $verify = verifyRequiredParams(array('id_user'), $param);
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        try {
            // INSERT INTO USER_GROUP
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $stmt = $this->db->prepare("INSERT INTO user_group (user_id, group_id ) "
                    . "VALUES (:id_user,:id_group)");
            // Asignar el valor del parámetro
            $stmt->bindParam(':id_user', $param['id_user']);
            $stmt->bindParam(':id_group', $id_group);

            // Ejecutar y obtener resultados
            $stmt->execute();
            $lastId = $this->db->lastInsertId();  // id of Insert new record

            $response->getBody()->write(json_encode(['message'=>$errorMessages['020']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(200);
         } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['message'=>$errorMessages['021']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }
    
    
    }
    public function EditUserGroup(Request $request, Response $response, $args)   
    {   
        global $errorMessages; 
    
    }
    public function DeleteUserGroup(Request $request, Response $response, $args)   
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
        
        $id_group = $args['group'];                      // Id del grupo seleccionado
        $id = $args['id'];                               // Id del registro a Borrar
        
        
        // Verificar que el ususario conectado es Administrador del grupo o es el propio usuario
        $stmt = $this->db->prepare("SELECT user_administrator FROM `group` WHERE id_group = :id_group".
                " AND user_administrator = $id_user");
        $stmt->bindParam(':id_group', $id_group);
        // Ejecutar y obtener resultados
        $stmt->execute();
        $dataGroup = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
        // Recupera el registro a eliminar
        $stmt = $this->db->prepare("SELECT * FROM user_group WHERE group_id = :id_group".
                " AND id_user_group = $id");
        $stmt->bindParam(':id_group', $id_group);
        // Ejecutar y obtener resultados
        $stmt->execute();
        $dataUserGroup = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
        
        // El usuario conectado no es el admin del Grupo o No es el ususario conectado el borrado
        custom_error(100,"Validación Delete User_Group: ".print_r($dataGroup,true).print_r($dataUserGroup,true)); // to Debug

        if ($dataUserGroup[0]['user_id'] <> $id_user) {
                if ( count($dataGroup) <> 1 ) {
                    $response->getBody()->write(json_encode(["message"=> $errorMessages['017']])); 
                    return $response
                        ->withHeader('content-type', 'application/json')
                        ->withStatus(400); 
                }
            }
        
        try {
            // DELETE INTO USER_GROUP
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $stmt = $this->db->prepare("DELETE FROM user_group ". 
                    " WHERE group_id = :id_group AND id_user_group = :id ");
            // Asignar el valor del parámetro
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_group', $id_group);

            // Ejecutar y obtener resultados
            $stmt->execute();

            if ( $stmt->rowCount() == 0 ) { // No se ha actualziado ningún registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['031']]));
                return $response
                     ->withHeader('content-type', 'application/json')
                     ->withStatus(200);
            }
                
            $response->getBody()->write(json_encode(['message'=>$errorMessages['022']]));
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
    
    // Acceso a las Secciones de un Grupo
    public function ListSectionGroup(Request $request, Response $response, $args)   
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
        
        $id_group = $args['group'];                      // Id del grupo seleccionado
        
        $id_user = $dataUser['id_user'];
        // Obtener todos los Secciones del Grupo
        $stmt = $this->db->prepare("SELECT id_section_group, section ".
            " FROM section_group WHERE  group_id = :id_group ORDER BY id_section_group ");
        $stmt->bindParam(':id_group', $id_group);
        // Ejecutar y obtener resultados
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
        
        $response->getBody()->write(json_encode(['data'=>$data]));
         return $response
             ->withHeader('content-type', 'application/json')
             ->withStatus(200);
    
    }
    public function AddSectionGroup(Request $request, Response $response, $args)   
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
        
        $id_group = $args['group'];                      // Id del grupo seleccionado
        //
        // Verificación de que están todos los campos
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        $verify = verifyRequiredParams(array('section'), $param);
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        try {
            // INSERT INTO section_group
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $stmt = $this->db->prepare("INSERT INTO section_group (section, group_id ) "
                    . "VALUES (:section,:id_group)");
            // Asignar el valor del parámetro
            $stmt->bindParam(':section', $param['section']);
            $stmt->bindParam(':id_group', $id_group);

            // Ejecutar y obtener resultados
            $stmt->execute();
            // $lastId = $this->db->lastInsertId();  // id of Insert new record

            $response->getBody()->write(json_encode(['message'=>$errorMessages['023']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(200);
         } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['message'=>$errorMessages['024']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }
    }
    public function EditSectionGroup(Request $request, Response $response, $args)   
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
        
        $id_group = $args['group'];                      // Id del grupo seleccionado
        $id = $args['id'];                               // Id del registro seleccionado
        // Verificación de que están todos los campos
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        $verify = verifyRequiredParams(array('section'), $param);
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        try {
            // UPDATE section_group
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $stmt = $this->db->prepare("UPDATE  section_group ".
                " set section = :section ".
                " WHERE id_section_group = :id AND group_id = :id_group ");
 
            // Asignar el valor del parámetro
            $stmt->bindParam(':section', $param['section']);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_group', $id_group);

            // Ejecutar y obtener resultados
            $stmt->execute();

            if ( $stmt->rowCount() == 0 ) { // No se ha actualziado ningún registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['031']]));
                return $response
                     ->withHeader('content-type', 'application/json')
                     ->withStatus(200);
            }

            $response->getBody()->write(json_encode(['message'=>$errorMessages['025']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(200);
         } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['message'=>$errorMessages['024']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }
    }
    public function DeleteSectionGroup(Request $request, Response $response, $args)   
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
        
        $id_group = $args['group'];                      // Id del grupo seleccionado
        $id = $args['id'];                               // Id del registro seleccionado
        
        try {
            // DELETE section_group
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $stmt = $this->db->prepare("DELETE  FROM section_group ".
                " WHERE id_section_group = :id AND group_id = :id_group ");
 
            // Asignar el valor del parámetro
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':id_group', $id_group);

            // Ejecutar y obtener resultados
            $stmt->execute();

            if ( $stmt->rowCount() == 0 ) { // No se ha actualizado ningún registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['031']]));
                return $response
                     ->withHeader('content-type', 'application/json')
                     ->withStatus(200);
            }

            $response->getBody()->write(json_encode(['message'=>$errorMessages['026']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(200);
         } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['message'=>$errorMessages['027']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }
    }
    
}