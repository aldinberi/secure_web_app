<?php

use \Firebase\JWT\JWT;

class UserDao extends BaseDao {

    public function get_by_id($id){
        $query = "SELECT id, name, email, phone, password
                  FROM user
                  WHERE id = :id";
        return parent::get_by_id($query, $id);
        
    }

    public function check_email($email){
        $query = "SELECT id, name, email, phone, password, sms_verify, otp_verify, account_verified, otp_secret, login_attempt
                  FROM user
                  WHERE email = :email";
        return parent::query($query, ['email' => $email]);
    }

    public function check_forgoten_email($email){
        $query = "SELECT id, email, account_verified
                  FROM user
                  WHERE email = :email";
        return parent::query($query, ['email' => $email]);
    }

    public function check_captcha($captcha_response){
        $data = array(
            'secret' => CHAPCHA_API_SECRET,
            'response' => $captcha_response
        );
        $verify = curl_init();
        
        curl_setopt($verify, CURLOPT_URL, "https://hcaptcha.com/siteverify");
        curl_setopt($verify, CURLOPT_POST, true);
        curl_setopt($verify, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($verify, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($verify, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($verify, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($verify);
        
        $response = json_decode($response, true);
        print_r($data);
        return $response['success'];

    }


    public function insert($user){

            if (gettype($user['name']) == "string") {
                $user['name'] = parent::test_input($user['name']);

                if (!preg_match("/^[a-zA-Z ]*$/",$user['name'])) {
                    throw new Exception("Only letters and white space allowed in name field");
                  }
                
            } else {
                throw new Exception('Full name is required');
            }
          
            if (gettype($user['email']) == "string") {
                $user['email'] = parent::test_input($user['email']);

                if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email format");
                  }
            } else {
                throw new Exception('Email is required');
            }

          
        return parent::insert('user', $user);
    }

    public function send_recovery_mail($email){

        if (gettype($email) == "string") {
            $email = parent::test_input($email);

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Invalid email format");
              }
        } else {
            throw new Exception('Email is required');
        }

        $user = $this->check_forgoten_email($email)[0];

        if($user){

            if(!$user['account_verified']){
                throw new Exception('You must first verify your account');
            }

            date_default_timezone_set("Europe/Helsinki");
            $jwt = JWT::encode(['exp' => time() + 3600, 'email' => $user['email'],  'id' => $user['id']], JWT_SECRET);

            $email_message = "If you have requested a password recovery click on this link\n";
            $email_message .= "https://sssd-ibu.herokuapp.com/passwordRecovery.html?jwt=".$jwt;

            $transport = (new Swift_SmtpTransport('smtp.sendgrid.net', 587))
            ->setUsername(SENDGRID_USERNAME)
            ->setPassword(SENDGRID_SECRET);

            $mailer = new Swift_Mailer($transport);

            $message = (new Swift_Message('IBU interns password recovey'))
            ->setFrom(['aldin.berisa@stu.ibu.edu.ba' => 'Aldin B'])
            ->setTo([$email, 'other@domain.org' => 'A name'])
            ->setBody($email_message);

            $result = $mailer->send($message);
            print_r($result);
            die;
        }
    }

    public function update_user($user){
        
        if($user['name']){
            if ( gettype($user['name']) == "string") {
                $user['name'] = parent::test_input($user['name']);

                if (!preg_match("/^[a-zA-Z ]*$/",$user['name'])) {
                    throw new Exception("Only letters and white space allowed in name field");
                }
                
            } else {
                throw new Exception('Full name is required');
            }
        }
        if($user['email'] ){
            if (gettype($user['email']) == "string") {
                $user['email'] = parent::test_input($user['email']);

                if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
                    throw new Exception("Invalid email format");
                }
            } else {
                throw new Exception('Email is required');
            }
        }

        return parent::update('user', $user);
    }   
    
    public function update_password($password){
        return parent::update('user', $password);
    }

}
?>