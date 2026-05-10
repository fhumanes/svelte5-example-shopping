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


