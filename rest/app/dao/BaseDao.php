<?php 
use OTPHP\TOTP;
class BaseDao {
    private $pdo;
    public function __construct()
    {
        
       
        $dsn = "mysql:host=".DB_HOST.";dbname=".DB_SCHEME.";";
        $opt = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $this->pdo = new PDO($dsn, DB_USER, DB_PASSWORD, $opt);
    }

    public function query($query, $params=[]) {
        $statment = $this->pdo->prepare($query);
        $statment->execute($params);
        return $statment->fetchAll();
    }

    public function bind( &$a, $val, $type )
	{
		$key = ':binding_'.count( $a );
        $a[$key] = $val;
		return $key;
    }
    
    public function test_input($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
      }

    public function verify_number($phone_number) {
        $phoneUtil = \libphonenumber\PhoneNumberUtil::getInstance();
        try {
            $PhoneNumberProto = $phoneUtil->parse($phone_number, "BA");
        } catch (\libphonenumber\NumberParseException $e) {
            return false;
        }
        return $phoneUtil->isValidNumber($PhoneNumberProto);

    }

    public function check_password($password){
        $hashed_password = strtoupper(sha1($password));
        $hash_first_5 = substr($hashed_password, 0, 5);
        $control_hash = substr($hashed_password, 5);
        $response = file_get_contents("https://api.pwnedpasswords.com/range/" .$hash_first_5);
        return strpos($response, $control_hash);
    }


    public function insert($table, $object){
        $columns = "";
        $params = "";
        foreach($object as $key => $value){
            $columns .= $key.",";
            $params .= ":".$key.",";
        }
        $columns = rtrim($columns, ',');
        $params = rtrim($params, ',');
        $insert = "INSERT INTO ".$table." (".$columns.") VALUES (".$params.")";

        $statment = $this->pdo->prepare($insert);
        $statment->execute($object);
        $object['id'] =  $this->pdo->lastInsertId(); 
        return $object;
    }

    public function update($table, $object) {
        $columns = "";
        foreach($object as $key => $value){
            if($key=="id")
                continue;
            $columns .= $key." =:".$key.", ";
        }
        $columns = rtrim($columns, ', ');
        $update = "UPDATE ".$table." SET ".$columns." WHERE id = :id ";
        $statment = $this->pdo->prepare($update);
        $statment->execute($object); 
        return $object;
        
    }

    public function delete($table, $id) {
        $delete = "DELETE FROM ".$table." WHERE id = :id ";   
        $statment = $this->pdo->prepare($delete);
        $statment->execute(['id' => $id]); 
    }

    public function get_by_id($query, $id) {
        $statment = $this->pdo->prepare($query);
        $statment->execute(['id' => $id]);
        return $statment->fetch();
        
    }
}
?>