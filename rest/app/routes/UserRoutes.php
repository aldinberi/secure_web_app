<?php

use \Firebase\JWT\JWT;
use OTPHP\TOTP;



Flight::before('start', function(&$params, &$output){
  $is_register = Flight::request()->url == '/register' || strpos(Flight::request()->url, '/register') === 0 && Flight::request()->method == 'POST';
  if ( Flight::request()->url != '/' && Flight::request()->url != '/login' && Flight::request()->url != '/users/recovery-email' && strpos(Flight::request()->url, '/login') !== 0 && !$is_register) {
    $headers = getallheaders();
    try {
      $decoded = (array)JWT::decode($headers['Authorization'], JWT_SECRET, array('HS256'));
      Flight::set('user', $decoded);
    } catch (\Exception $e) {
        Flight::halt(403, json_encode(['msg' => "The token is not valid"]));
        die;
    }
  }
}); 


/**
 * @OA\Post(
 *      path="/register",
 *      tags={"register"},
 *      summary="API for inserting user to database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/UserInsert")
 *       ),
 *      
 *     
 *      @OA\Response(
 *           response=200,
 *           description="User registered successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The user couldn't be registered.",
 *      ),

 * )
 */


Flight::route('POST /register', function () {
    $dao = new UserDao();
    $request = Flight::request();
    $user = $request->data->getData();
    if(!$dao->check_captcha($user['h-captcha-response'])){
      Flight::halt(403, json_encode(['msg'=>"Invalid Captcha, please try again"]));
    }
    if(sizeof($dao->check_email($user['email'])) == 0){

      try {
       
        $basic  = new \Nexmo\Client\Credentials\Basic(NEXMO_API_KEY, NEXMO_API_SECRET);
        $client = new \Nexmo\Client($basic);


        if($dao->check_password($user['password']))
            throw new Exception('You can not use a weak password, try a new one');
        if(!$dao->verify_number($user['phone'])){
          throw new Exception('Invalid phone number, try a new one');
        }
        $otp = TOTP::create();
        $otp->setLabel("IBU Interns");
        print_r( $otp->getSecret());
        $user['otp_secret'] = $otp->getSecret();
        $client->message()->send([
          'to' => $user['phone'],
          'from' => 'IBU Interns',
          'text' => 'Activate using code: '.$otp->getSecret()
        ]);
        $allowed_fields = ["name", "email", "phone", "password", "otp_secret"];     
        $user['password'] = password_hash($user['password'], PASSWORD_DEFAULT);
        $dao->insert(array_intersect_key($user, array_flip($allowed_fields)));
        unset($user['password']);

        $jwt = JWT::encode(['email' => $user['email'], 'account_verified'=> 0 ], JWT_SECRET);
        Flight::output(["token" => $jwt]);

      } catch (Exception $e) {
        Flight::halt(500, json_encode(['msg'=>$e->getMessage()]));
      }

    }else{
        Flight::halt(403, json_encode(['msg'=>'Email already taken']));
    }
});

/**
 * @OA\Post(
 *      path="/login",
 *      tags={"login"},
 *      summary="API for loging in user.",
 *     @OA\RequestBody(
 *          description="Request body.",
 *          @OA\JsonContent(ref="#/components/schemas/LoginUser")
 *       ),
 *       
 *      @OA\Response(
 *           response=200,
 *           description="Successful login",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The user couldn't be logged in.",
 *      )
 * )
 */

Flight::route('POST /login', function(){
    $dao = new UserDao();
    $login_data = Flight::request()->data->getData();
    $response_data = $dao->check_email($login_data['email']);
 
    
    if(count($response_data) == 1){
      $user =$response_data[0];
      if($user['login_attempt'] >= 3 && !$dao->check_captcha($login_data['h-captcha-response'])){
        Flight::halt(403, json_encode(['msg'=>"Too many logins with account, fill captcha please try again", 'login_attempt' => $user['login_attempt']+1]));
      }
      if(password_verify($login_data['password'], $user['password'])){
        try {
          $remember = (array)JWT::decode($login_data["remember_token"], JWT_SECRET, array('HS256'));
          if(in_array($user['id'], $remember)){
              $dao->update_user(['id'=> $user['id'], 'login_attempt' => 0]);
              unset($user['password']);
              unset($user['otp_secret']);

              $jwt = JWT::encode($user, JWT_SECRET);
              Flight::output(["token" => $jwt]);
          } 
        } catch (Exception $e) {}
        if ($user['sms_verify']){
          $basic  = new \Nexmo\Client\Credentials\Basic(NEXMO_API_KEY, NEXMO_API_SECRET);
          $client = new \Nexmo\Client($basic);
  
          $verification = $client->verify()->start([ 
            'number' => $user['phone'],
            'brand'  => 'IBU Interns',
            'code_length'  => '6']);

         $jwt = JWT::encode(['email' => $user['email'],  'verification_id' => $verification->getRequestId(), 'sms_verify' => $user["sms_verify"], 'account_verified'=> $user["account_verified"]], JWT_SECRET);
  
          Flight::output(["token" => $jwt]);

        } else if($user['otp_verify'] || !$user['account_verified']){

          $jwt = JWT::encode(['email' => $user['email'], 'otp_verify'=>$user['otp_verify']], JWT_SECRET);
          Flight::output(["token" => $jwt]);

        } else {
          $dao->update_user(['id'=> $user['id'], 'login_attempt' => 0]);
          unset($user['password']);
          unset($user['otp_secret']);
          $jwt = JWT::encode($user, JWT_SECRET);
          Flight::output(["token" => $jwt]);

        }


      }else{
        $dao->update_user(['id'=> $user['id'], 'login_attempt' => $user['login_attempt']+1]);
        Flight::halt(403, json_encode(['msg'=>"Invalid email or password", 'login_attempt' => $user['login_attempt']+1]));
      }
  
    }else{
      Flight::halt(403, json_encode(['msg'=>"Invalid email or password"]));
    }
  });

  /**
 * @OA\Post(
 *      path="/login/verify",
 *      tags={"login"},
 *      summary="API for inserting user to database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/UserLoginVerify")
 *       ),
 *      
 *     
 *      @OA\Response(
 *           response=200,
 *           description="User verified successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The user couldn't be verified.",
 *      ),

 * )
 */

Flight::route('POST /login/verify', function () {
  $dao = new UserDao();
  $request = Flight::request();
  $code = $request->data->getData()['code'];
  $headers = getallheaders();
  $token = (array)JWT::decode($headers['Authorization'], JWT_SECRET, array('HS256'));
  
  $response_data = $dao->check_email($token['email']);
 
  try {
    $user =$response_data[0];
    if($user['sms_verify']){
        $request_id = $token['verification_id'];
        $basic  = new \Nexmo\Client\Credentials\Basic(NEXMO_API_KEY, NEXMO_API_SECRET);
        $client = new \Nexmo\Client($basic);
        $verification = new \Nexmo\Verify\Verification($request_id);
      
    
        try{
          $result = $client->verify()->check($verification, $code);
          $dao->update_user(['id'=> $user['id'], 'login_attempt' => 0]);
          unset($user['password']);
          unset($user['otp_secret']);
          $jwt = JWT::encode($user, JWT_SECRET);
          Flight::output(["token" => $jwt]);
    
    
        } catch (\Exception $e) {
          Flight::halt(403, json_encode(['msg' => "Invalid code, try again"]));
          die;
        }
    } else if($user['otp_verify']){
      $otp = TOTP::create($user['otp_secret']);
      $otp->setLabel("IBU Interns");
    
      if($otp->verify($code)){
          $dao->update_user(['id'=> $user['id'], 'login_attempt' => 0]);
          unset($user['password']);
          unset($user['otp_secret']);
          $jwt = JWT::encode($user, JWT_SECRET);
          Flight::output(["token" => $jwt]);
      }else{
        Flight::halt(403, json_encode(['msg' => "Invalid code, try again"]));
        die;
      }
    }

  
  } catch (\Exception $e) {
      Flight::halt(403, json_encode(['msg' => "Invalid access"]));
      die;
  }

});

 /**
 * @OA\Post(
 *      path="/register/verify",
 *      tags={"register"},
 *      summary="API for inserting user to database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/UserRegisterVerify")
 *       ),
 *      
 *     
 *      @OA\Response(
 *           response=200,
 *           description="User verified successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The user couldn't be verified.",
 *      ),

 * )
 */

Flight::route('POST /register/verify', function () {
  $dao = new UserDao();
  $request = Flight::request();
  $code = $request->data->getData()['code'];
  $headers = getallheaders();
  $token = (array)JWT::decode($headers['Authorization'], JWT_SECRET, array('HS256'));
      
  try {

      $user = $dao->check_email($token['email']);
      $otp = TOTP::create($user[0]['otp_secret']);
      $otp->setLabel("IBU Interns");
    
      if($otp->verify($code)){
        $dao->update_user(['account_verified' => 1, 'id' => $user[0]['id']]); 
        $user[0]['account_verified'] =1;
      }else{
        Flight::halt(403, json_encode(['msg' => "Invalid code, try again"]));
        die;
      }
      
      unset($user[0]['otp_secret']);
      unset($user[0]['password']);
      $jwt = JWT::encode($user[0], JWT_SECRET);
      Flight::output(["token" => $jwt]);
      Flight::json($user[0]);      


 
  
  } catch (\Exception $e) {
      Flight::halt(403, json_encode(['msg' => "Invalid access"]));
      die;
  }

});

/**
 * @OA\Put(
 *      path="/users/{id}",
 *      tags={"user"},
 *      summary="API for updating user in database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/UserInsert")
 *       ),
 *    @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of user",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Response(
 *           response=200,
 *           description="User updated successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The user couldn't be updated.",
 *      ),
 *     security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('PUT /users/@id', function($id){
  $dao = new UserDao();
  $request = Flight::request();
  $user = $request->data->getData();
  $user['id'] = $id;
  if($user['remember_me']){
    try{
      $remember = (array)JWT::decode($user["remember_token"], JWT_SECRET, array('HS256'));
      if(!in_array($user['id'], $remember)){
        if(count($remember) == 0){
          $remember[0] = $user['id'];
        }else{
          $remember[count($remember) + 1] = $user['id'];  
        }    
      } 
    } catch (\Exception $e) {
      $remember[0] = $user['id']; 
    }   
    $user["remember_token"] = JWT::encode($remember, JWT_SECRET);
  } else {
    try{
      $remember = (array)JWT::decode($user["remember_token"], JWT_SECRET, array('HS256'));
      $key = array_search($user['id'], $remember);
      if($key || $key === 0){
        unset($remember[$key]); 
        $user["remember_token"] = JWT::encode($remember, JWT_SECRET);        
      } 
    } catch (\Exception $e) {} 
  }

  try {
      if(!$dao->verify_number($user['phone'])){
        throw new Exception('Invalid phone number, try a new one');
      }
      $allowed_fields = ["name", "email", "sms_verify", "phone", "otp_verify", "id"];
      $dao->update_user(array_intersect_key($user, array_flip($allowed_fields)));
      Flight::output($user);
  } catch (Exception $e) {
    Flight::halt(500, json_encode(['msg'=>$e->getMessage()]));
}
});

/**
 * @OA\Put(
 *      path="/users/password/{id}",
 *      tags={"user"},
 *      summary="API for updating user password in database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/UserPasswordUpdate")
 *       ),
 *    @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of user",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Response(
 *           response=200,
 *           description="User password updated successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The user password couldn't be updated.",
 *      ),
 *     security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('PUT /users/password/@id', function($id){
  $dao = new UserDao();
  $request = Flight::request();
  $data = $request->data->getData();
  $user = $dao->get_by_id($id);   
  if(password_verify($data['old_password'], $user['password'])){
      try {

        if($dao->check_password($data['new_password']))
            throw new Exception('You can not use a weak password, try a new one');
        $dao->update_password(['password' => password_hash($data['new_password'], PASSWORD_DEFAULT), 'id' => $id]);
        
      } catch (Exception $e) {
        Flight::halt(500, json_encode(['msg'=>$e->getMessage()]));
      }

     
  }else{
      Flight::halt(403, json_encode(['msg'=>'Entered worng old password']));
  }
  Flight::output($data);
});


/**
 * @OA\Put(
 *      path="/users/password-recover/{id}",
 *      tags={"user"},
 *      summary="API for recovering user password in database.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/UserPasswordUpdate")
 *       ),
 *    @OA\Parameter(
 *          name="id",
 *          in="path",
 *          required=true,
 *          description="ID of user",
 *          @OA\Schema(
 *              type="integer",
 *              default=0
 *          )
 *      ),
 *      @OA\Response(
 *           response=200,
 *           description="User password updated successfully",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="The user password couldn't be updated.",
 *      ),
 *     security={
 *          {"api_key": {}}
 *      }
 * )
 */

Flight::route('PUT /users/password-recover/@id', function($id){
  $dao = new UserDao();
  $request = Flight::request();
  $data = $request->data->getData();  
  try {

    if($dao->check_password($data['password']))
        throw new Exception('You can not use a weak password, try a new one');
    $dao->update_password(['password' => password_hash($data['password'], PASSWORD_DEFAULT), 'id' => $id]);
    $dao->update_user(['id'=> $id, 'login_attempt' => 0]);
        
  } catch (Exception $e) {
    Flight::halt(500, json_encode(['msg'=>$e->getMessage()]));
  }
});

 /**
 * @OA\Post(
 *      path="/users/recovery-email",
 *      tags={"user"},
 *      summary="API for sending recovey mail to the user.",
 *     @OA\RequestBody(
 *          description="Sample request body.",
 *          @OA\JsonContent(ref="#/components/schemas/UserSendEmail")
 *       ),
 *      
 *     
 *      @OA\Response(
 *           response=200,
 *           description="Successfully send email",
 *      ),
 *      @OA\Response(
 *           response=400,
 *           description="Email couldn't be send.",
 *      ),

 * )
 */

Flight::route('POST /users/recovery-email', function(){
  $dao = new UserDao();
  $request = Flight::request();
  $response_data = $request->data->getData();
  try {
    $dao->send_recovery_mail($response_data['email']);
    
  } catch (Exception $e) {
    Flight::halt(500, json_encode(['msg'=>$e->getMessage()]));
  }
});

