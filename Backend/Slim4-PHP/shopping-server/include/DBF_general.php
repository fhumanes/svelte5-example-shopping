<?php
    
    use Psr\Http\Message\ServerRequestInterface as Request;
    use Psr\Http\Message\ResponseInterface as Response;

trait General {

    // Acceso a los Unidades de Medida
    public function ListMeasure(Request $request, Response $response, $args)   
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
        /*
        $uuid = $verify['token-user']; 
        $session = $this->openSession($uuid);   // Recuperación datos usuario conectado
        $dataUser = $session['dataUser'];
         */
 
        // Obtener todos los unit_measure"
        $stmt = $this->db->prepare("SELECT id_unit_measure, title FROM unit_measure order by title");
        // Ejecutar y obtener resultados
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
        
        $response->getBody()->write(json_encode(['data'=>$data]));
         return $response
             ->withHeader('content-type', 'application/json')
             ->withStatus(200);
    
    }
    // ADD de las Unidades de Medida
    public function AddMeasure(Request $request, Response $response, $args)   
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
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        $verify = verifyRequiredParams(array('title'), $param);
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        try {
            // INSERT INTO UNIT_MEASURE
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $stmt = $this->db->prepare("INSERT INTO unit_measure (title) "
                    . "VALUES (:title)");
            // Asignar el valor del parámetro
            $stmt->bindParam(':title', $param['title']);

            // Ejecutar y obtener resultados
            $stmt->execute();
            $lastId = $this->db->lastInsertId();  // id of Insert new record

            $response->getBody()->write(json_encode(['message'=>$errorMessages['028']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(200);
         } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['message'=>$errorMessages['029']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }
    
    
    }
    // EDIT de las Unidades de Medida
    public function EditMeasure(Request $request, Response $response, $args)   
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
        
        $id = $args['id'];                              // Id del unit_measure

     
        // El usuario conectado es Administrator?
        if ($is_administrator <> 1 ) {
            $response->getBody()->write(json_encode(["message"=> $errorMessages['017']])); 
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400); 
        }
        // Verificación de que están todos los campos
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        $verify = verifyRequiredParams(array('title'), $param);
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        try {
            // UPDATE  UNIT_MEASURE
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $stmt = $this->db->prepare("UPDATE unit_measure set title = "
                    . ":title WHERE id_unit_measure = :id " );
            // Asignar el valor del parámetro
            $stmt->bindParam(':title', $param['title']);
            $stmt->bindParam(':id', $id);

            // Ejecutar y obtener resultados
            $stmt->execute();
            
            if ( $stmt->rowCount() == 0 ) { // No se ha actualziado ningún registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['031']]));
                return $response
                     ->withHeader('content-type', 'application/json')
                     ->withStatus(200);
            }
                
            $response->getBody()->write(json_encode(['message'=>$errorMessages['030']]));
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
    // Delete de las Unidades de Medida
    public function DeleteMeasure(Request $request, Response $response, $args)   
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
        
        $id = $args['id'];                              // Id del unit_measure
 
        // El usuario conectado es Administrator?
        if ($is_administrator <> 1 ) {
            $response->getBody()->write(json_encode(["message"=> $errorMessages['017']])); 
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400); 
        }

        try {
            // DELETE UNIT_MEASURE
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $stmt = $this->db->prepare("DELETE FROM unit_measure WHERE id_unit_measure = :id " );
            // Asignar el valor del parámetro
            $stmt->bindParam(':id', $id);

            // Ejecutar y obtener resultados
            $stmt->execute();
            
            if ( $stmt->rowCount() == 0 ) { // No se ha actualziado ningún registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['031']]));
                return $response
                     ->withHeader('content-type', 'application/json')
                     ->withStatus(200);
            }
                
            $response->getBody()->write(json_encode(['message'=>$errorMessages['032']]));
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

    

    // Acceso a la Plantilla de Secciones "section_template"
    public function ListSectionTemplate(Request $request, Response $response, $args)   
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
        /*
        $uuid = $verify['token-user']; 
        $session = $this->openSession($uuid);   // Recuperación datos usuario conectado
        $dataUser = $session['dataUser'];
         */
 
        // Obtener todos los section_templates"
        $stmt = $this->db->prepare("SELECT id_section_template, section FROM section_template ORDER BY id_section_template");
        // Ejecutar y obtener resultados
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
        
        $response->getBody()->write(json_encode(['data'=>$data]));
         return $response
             ->withHeader('content-type', 'application/json')
             ->withStatus(200);
    
    }
    // ADD de las Plantilla de secciones
    public function AddSectionTemplate(Request $request, Response $response, $args)   
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
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        $verify = verifyRequiredParams(array('section'), $param);
        if ($verify['error'] == true ){ // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        try {
            // INSERT INTO UNIT_MEASURE
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $stmt = $this->db->prepare("INSERT INTO section_template (section) "
                    . "VALUES (:section)");
            // Asignar el valor del parámetro
            $stmt->bindParam(':section', $param['section']);

            // Ejecutar y obtener resultados
            $stmt->execute();
            $lastId = $this->db->lastInsertId();  // id of Insert new record

            $response->getBody()->write(json_encode(['message'=>$errorMessages['033']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(200);
         } catch (PDOException $e) {
            $response->getBody()->write(json_encode(['message'=>$errorMessages['034']]));
             return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
         }  
    }
    // EDIT de las Secciones de Plantilla
    public function EditSectionTemplate(Request $request, Response $response, $args)   
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
        
        $id = $args['id'];                              // Id de la sección

     
        // El usuario conectado es Administrator?
        if ($is_administrator <> 1 ) {
            $response->getBody()->write(json_encode(["message"=> $errorMessages['017']])); 
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400); 
        }
        // Verificación de que están todos los campos
        $param = $request->getParsedBody();          // Obtener los datos del JSON
        $verify = verifyRequiredParams(array('section'), $param);
        if ($verify['error'] == true ){              // Se ha encontrado error en la verificación
            $response->getBody()->write(json_encode(["message"=> $verify['message']]));
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus($verify['error_status']);   
        }
        try {
            // UPDATE  section_template
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $stmt = $this->db->prepare("UPDATE section_template set section = "
                    . ":section WHERE id_section_template = :id " );
            // Asignar el valor del parámetro
            $stmt->bindParam(':section', $param['section']);
            $stmt->bindParam(':id', $id);

            // Ejecutar y obtener resultados
            $stmt->execute();
            
            if ( $stmt->rowCount() == 0 ) {     // No se ha actualizado ningún registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['031']]));
                return $response
                     ->withHeader('content-type', 'application/json')
                     ->withStatus(200);
            }
                
            $response->getBody()->write(json_encode(['message'=>$errorMessages['035']]));
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
    // Delete de las Unidades de Medida
    public function DeleteSectionTemplate(Request $request, Response $response, $args)   
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
        
        $id = $args['id'];                              // Id del unit_measure
 
        // El usuario conectado es Administrator?
        if ($is_administrator <> 1 ) {
            $response->getBody()->write(json_encode(["message"=> $errorMessages['017']])); 
            return $response
                ->withHeader('content-type', 'application/json')
                ->withStatus(400); 
        }

        try {
            // DELETE section_template
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $stmt = $this->db->prepare("DELETE FROM section_template WHERE id_section_template = :id " );
            // Asignar el valor del parámetro
            $stmt->bindParam(':id', $id);

            // Ejecutar y obtener resultados
            $stmt->execute();
            
            if ( $stmt->rowCount() == 0 ) { // No se ha actualziado ningún registro
                $response->getBody()->write(json_encode(['message'=>$errorMessages['031']]));
                return $response
                     ->withHeader('content-type', 'application/json')
                     ->withStatus(200);
            }
                
            $response->getBody()->write(json_encode(['message'=>$errorMessages['036']]));
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
// Informe de Lista de Compra
    public function jsonInforme001(Request $request, Response $response, $args)   
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

        
        $idGroup = $args['idgroup'];                    // Id del Grupo
 
        // El usuario conectado es del Grupo?
        $stmt = $this->db->prepare("SELECT * FROM user_group WHERE user_id = :id AND group_id = :idGroup" );
        // Asignar el valor del parámetro
        $stmt->bindParam(':id', $id_user);
        $stmt->bindParam(':idGroup', $idGroup);
        // Ejecutar y obtener resultados
        $stmt->execute();
        $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros

        if ( count($data) == 0 ) { // No se ha recuperado ningún registro. El usuario no es del Grupo
            $response->getBody()->write(json_encode(['message'=>$errorMessages['048']]));
            return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
        }

        try {
            // Recupera los datos de la lista de Compra
            $now = date('Y-m-d H:i:s');         // Fecha actual
            $hoy = date('d/m/Y');               // fecha de impresión
            $respuesta = array();
            // Datos de Cabecera
            $stmt = $this->db->prepare("SELECT name as grupo FROM `group` WHERE id_group = :idGroup" );
            $stmt->bindParam(':idGroup', $idGroup);
            // Ejecutar y obtener resultados
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
            $data[0]['fecha'] = $hoy;
            $respuesta['Cabecera'] = $data[0];  // Cargo los datos de cabecera
            
            // Datos de Secciones y Productos
    
            $stmt = $this->db->prepare("SELECT section, name, amount, title, photo, photo_mime, purchased ".
                " FROM product ".
                " JOIN section_group on ( product.group_id = section_group.group_id AND id_section_group = section_group_id )".
                " JOIN unit_measure on (id_unit_measure = unit_measure_id)".
                " WHERE purchased = 0   AND product.group_id = :idGroup ".
                " ORDER BY 1,2 " );
            $stmt->bindParam(':idGroup', $idGroup);
            // Ejecutar y obtener resultados
            $stmt->execute();
            $data = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos los registros
            
            if ( count($data) == 0 ) { // No se ha recuperado ningún registro. El usuario no es del Grupo
            $response->getBody()->write(json_encode(['message'=>$errorMessages['049']]));
            return $response
                 ->withHeader('content-type', 'application/json')
                 ->withStatus(400);
            }
            // Agrupación por sección
            $agrupado = [];

            foreach ($data as $row) {
                $seccion = $row["section"];
                // Si la sección no existe, la creamos
                if (!isset($agrupado[$seccion])) {
                    $agrupado[$seccion] = [
                        "seccion" => $seccion,
                        "articulos" => []
                    ];
                }
                 // Convertir la foto si existe 
                 $fotoBase64 = null; 
                 if (!empty($row["photo"])) { 
                     $mime = $row["photo_mime"] ?? "application/octet-stream"; 
                     $fotoBase64 = "data:" . $mime . ";base64," . base64_encode($row["photo"]);    
                 }

                // Añadimos el artículo dentro de la sección
                $agrupado[$seccion]["articulos"][] = [
                    "name" => $row["name"],
                    "amount" => $row["amount"],
                    "title" => $row["title"],
                    "photo" => $fotoBase64 // ← ya convertido o null
                ];
            }
            // Convertimos a array indexado para JSON
            $respuesta['lineas'] = array_values($agrupado);

            $response->getBody()->write(json_encode(['data'=>$respuesta]));
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