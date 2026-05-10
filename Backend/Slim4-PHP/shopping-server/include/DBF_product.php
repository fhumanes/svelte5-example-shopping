<?php
    
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;

trait Product {
    
    // Acceso a los Productos de un Grupo
    public function ListProduct(Request $request, Response $response, $args)   
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
        // Obtener todos los usuarios del Grupo  ejemplo: REPLACE(TO_BASE64(photo), '\n', '') AS photo
        $stmt = $this->db->prepare(
            "SELECT ".
            " id_product, product.group_id, section_group_id, section, name, amount, unit_measure_id, title, ". 
            " REPLACE(TO_BASE64(photo), '\n', '') AS photo, photo_file, photo_mime, user_last_change, date_last_change, user_last_buy, date_last_buy, purchased ".
            " FROM product ".
            " JOIN unit_measure ON (id_unit_measure = unit_measure_id) ".
            " JOIN section_group ON (id_section_group = section_group_id) ".
            " WHERE product.group_id = :id_group ");
        $stmt->bindParam(':id_group', $id_group);
        // Ejecutar y obtener resultados
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
        /*
        // Convertir el campo "photo" a Base64 en cada registro 
        foreach ($data as &$row) 
        { 
            if (!empty($row['photo'])) 
            { 
                $row['photo'] = base64_encode($row['photo']); 
            } 
        }
        */
        $response->getBody()->write(json_encode(['data'=>$data]));
         return $response
             ->withHeader('content-type', 'application/json')
             ->withStatus(200);
    
    }
    
    public function AddProduct(Request $request, Response $response, $args)   
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
         
        // Verificación de que están todos los campos 
        // "section_group_id, name, amount, unit_measure_id, photo, photo_file, photo_mime"
        // Campos Obligatorios, PHOTO ..., NO ES OBLIGATORIO
        
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        $verify = verifyRequiredParams(array('section_group_id', 'name', 'amount', 'unit_measure_id'), $param); 
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        try {
            // INSERT INTO PRODUCT
            $now = date('Y-m-d H:i:s');         // Fecha actual
            
            if (!isset($param['photo']) ){ // Para identificar si existe foto
                $param['photo'] = null;
            }
            if ( $param['photo'] == null ) {
                $param['photo_file'] = null;
                $param['photo_mime'] = null;  
            } else {
                $param['photo'] = base64_decode($param['photo']);       // Pasar el fichero a binario
            }
            $stmt = $this->db->prepare("INSERT INTO product ".
                    "(group_id, section_group_id, name, amount, unit_measure_id, ".
                    "photo, photo_file, photo_mime, ".
                    " user_last_change, date_last_change, user_last_buy, date_last_buy, purchased) ".
                    "VALUES (:id_group, :id_section_group, :name, :amount, :id_unit_measure, ".
                    ":photo, :photo_file, :photo_mime, ".
                    " :id_user, :now, null, :now2, '0')");
            // Asignar el valor del parámetro
            $stmt->bindParam(':id_group', $id_group);
            $stmt->bindParam(':id_section_group', $param['section_group_id']);
            $stmt->bindParam(':name', $param['name']);
            $stmt->bindParam(':amount', $param['amount']);
            $stmt->bindParam(':id_unit_measure', $param['unit_measure_id']);
            $stmt->bindParam(':photo', $param['photo']);
            $stmt->bindParam(':photo_file', $param['photo_file']);
            $stmt->bindParam(':photo_mime', $param['photo_mime']);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->bindParam(':now', $now);
            $stmt->bindParam(':now2', $now);

            // Ejecutar y obtener resultados
            $stmt->execute();
            $lastId = $this->db->lastInsertId();  // id of Insert new record

            $response->getBody()->write(json_encode(['message'=>$errorMessages['037']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(200);
         } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['message'=>$errorMessages['038']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }
    
    
    }
    public function EditProduct(Request $request, Response $response, $args)   
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
        
        $id_product = $args['id'];                       // Id del product
         
        // Verificación de que están todos los campos 
        // "section_group_id, name, amount, unit_measure_id, photo, photo_file, photo_mime"
        // Campos Obligatorios, PHOTO ..., NO ES OBLIGATORIO
        
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        $verify = verifyRequiredParams(array('section_group_id', 'name', 'amount', 'unit_measure_id'), $param); 
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        try {
            // Update PRODUCT
            $now = date('Y-m-d H:i:s');         // Fecha actual
            
            if (!isset($param['photo']) ){ // Para identificar si existe foto
                $param['photo'] = null;
            }
            if ( $param['photo'] == null ) {
                $param['photo_file'] = null;
                $param['photo_mime'] = null;  
            } else {
                $param['photo'] = base64_decode($param['photo']);       // Pasar el fichero a binario
            }
            $stmt = $this->db->prepare("UPDATE  product SET ".
                    " section_group_id = :id_section_group, " .
                    " name = :name, ".
                    " amount = :amount, ".
                    " unit_measure_id = :id_unit_measure, ".
                    " photo = :photo, photo_file = :photo_file , photo_mime = :photo_mime, ".
                    " user_last_change = :id_user , date_last_change = :now  ".
                    " WHERE id_product = :id_product AND group_id = :id_group");
            // Asignar el valor del parámetro
            $stmt->bindParam(':id_group', $id_group);
            $stmt->bindParam(':id_section_group', $param['section_group_id']);
            $stmt->bindParam(':name', $param['name']);
            $stmt->bindParam(':amount', $param['amount']);
            $stmt->bindParam(':id_unit_measure', $param['unit_measure_id']);
            $stmt->bindParam(':photo', $param['photo']);
            $stmt->bindParam(':photo_file', $param['photo_file']);
            $stmt->bindParam(':photo_mime', $param['photo_mime']);
            $stmt->bindParam(':id_user', $id_user);
            $stmt->bindParam(':now', $now);
            $stmt->bindParam(':id_product', $id_product);

            // Ejecutar y obtener resultados
            $stmt->execute();
            
            if ( $stmt->rowCount() == 0 ) { // No se ha actualizado ningún registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['031']]));
                return $response
                     ->withHeader('content-type', 'application/json')
                     ->withStatus(400);
            }
              

            $response->getBody()->write(json_encode(['message'=>$errorMessages['039']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(200);
         } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['message'=>$errorMessages['038']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }
    }
    public function EditProductState(Request $request, Response $response, $args)   
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
        
        $id_product = $args['id'];                       // Id del product
        $state = $args['state'];                         // El nuevo estado "pending" = 0, "purchased" = 1
        
        if (!in_array($state,['0','1'])) { // Comprobar que es alguno de los valores posibles
            $response->getBody()->write(json_encode(['message'=>$errorMessages['040']]));
            return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
        }
        
        try {
            // Update PRODUCT
            $now = date('Y-m-d H:i:s');         // Fecha actual
            if ($state == '0') {         
                $stmt = $this->db->prepare("UPDATE  product SET ".
                        " purchased = 0 " .
                        " WHERE id_product = :id_product AND group_id = :id_group");
                $stmt->bindParam(':id_product', $id_product);
                $stmt->bindParam(':id_group', $id_group);
                
            } else {
                 $stmt = $this->db->prepare("UPDATE  product SET ".
                        " purchased = 1 ," .
                        " user_last_buy = :id_user, ".
                        " date_last_buy = :now ".
                        " WHERE id_product = :id_product AND group_id = :id_group");
                $stmt->bindParam(':id_product', $id_product);
                $stmt->bindParam(':id_group', $id_group);
                $stmt->bindParam(':id_user', $id_user);
                $stmt->bindParam(':now', $now);
                               
            }
            // Ejecutar y obtener resultados
            $stmt->execute();
            
            if ( $stmt->rowCount() == 0 ) { // No se ha actualizado ningún registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['031']]));
                return $response
                     ->withHeader('content-type', 'application/json')
                     ->withStatus(400);
            }
              
            $response->getBody()->write(json_encode(['message'=>$errorMessages['039']]));
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
    public function DeleteProduct(Request $request, Response $response, $args)   
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
        
        $id_product = $args['id'];                       // Id del product
        
        try {
            // DELETE PRODUCT
            $now = date('Y-m-d H:i:s');         // Fecha actual
       
            $stmt = $this->db->prepare("DELETE  FROM product  ".
                    " WHERE id_product = :id_product AND group_id = :id_group");
            $stmt->bindParam(':id_product', $id_product);
            $stmt->bindParam(':id_group', $id_group);       
            // Ejecutar y obtener resultados
            $stmt->execute();
            
            if ( $stmt->rowCount() == 0 ) { // No se ha actualizado ningún registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['031']]));
                return $response
                     ->withHeader('content-type', 'application/json')
                     ->withStatus(400);
            }
              
            $response->getBody()->write(json_encode(['message'=>$errorMessages['041']]));
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
