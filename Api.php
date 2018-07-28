<?php
    class Api extends Rest{
        public $dbConn;
        public function __construct(){
            # llamo al constructor de rest
            parent::__construct();
        }

        public function generateToken(){
            # genero el token
            # para generar el token necesito saber el email y el password del usuario
            $email = $this->validateParameter('email', $this->param['email'], _STRING);
            $password = $this->validateParameter('pass', $this->param['pass'], _STRING);
            
            $stmt = $this->dbConn->prepare('SELECT * FROM users WHERE email = :email AND password = :pass');
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':pass', $password);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            # si no existe, devuelvo un error
            if (!is_array($user)) {
                $this->returnResponse(INVALID_USER_PASS, "Email or Password incorrect");
            }
            # si no esta activo, devuelvo un error
            if ($user['active'] == 0) {
                $this->returnResponse(USER_NOT_ACTIVE, "User is not activated, please contact to admin");
            }
            # en caso de que todo este bien, creo el payload (3° parte del token JWT)
            $payload = [
                'iat' => time(), # creado en
                'iss' => 'localhost', # creador por
                'exp' => time() + (15*60), # expira en
                'userID' => $user['id'] # id del usuario
            ];
            # creo el token
            $token = JWT::encode($payload, SECRET_KEY);
            # devuelvo el token creado
            $data = ['token' => $token];
            $this->returnResponse(SUCCESS_RESPONSE, $data);
        }

        public function addCustomer(){
            try{
                # valido y seteo las variables
                $name = $this->validateParameter('name', $this->param['name'], _STRING);
                $email = $this->validateParameter('email', $this->param['email'], _STRING);
                $addr = $this->validateParameter('addr', $this->param['addr'], _STRING);
                $mobile = $this->validateParameter('mobile', $this->param['mobile'], _INTEGER);

                # seteo las propiedades de customers
                $cust = new Customer;
                $cust->setName($name);
                $cust->setEmail($email);
                $cust->setAddress($addr);
                $cust->setMobile($mobile);
                $cust->setCreatedBy($this->userId);
                $cust->setCreatedOn(date('Y-m-d'));

                # verifico si insero o no
                if (!$cust->insert()){
                    $message = 'Failed to insert';
                } else {
                    $message = 'Successfully Inserted';
                }
            
            # si todo sale bien devuelvo success
            $this->returnResponse(SUCCESS_RESPONSE, $message);

            } catch (Exception $e){
                # devuelvo error en caso de falla en la DB
                $this->throwError(DATABASE_ERROR, $e->getMessage());
            }
        }

        public function getCustomersDetails(){

            try{
                # para retornar un customer necesito que me entreguen la id
                $customerId = $this->validateParameter('customerId', $this->param['customerId'], _INTEGER);

                $cust = new Customer;
                $cust->setId($customerId);
                $customer = $cust->getCustomerDetailsById();
                if (!is_array($customer)) {
                    $this->returnResponse(SUCCESS_RESPONSE, ['message' => 'Customer details not found']);
                } 
                
                # en caso de encontrar el customer, seteo una variable response con los datos y los devuelvo
                $response['customerId']     = $customer['id'];
                $response['email']          = $customer['email'];
                $response['address']        = $customer['address'];
                $response['mobile']         = $customer['mobile'];
                $response['created_on']     = $customer['created_on'];
                $response['created_user']   = $customer['created_user'];
                
                $this->returnResponse(SUCCESS_RESPONSE, $response);

            } catch (Exception $e){
                $this->throwError(DATABASE_ERROR, $e->getMessage());
            }
           
        }

        public function updateCustomer(){
            try{
                # valido y seteo las variables
                $customerId = $this->validateParameter('customerId', $this->param['customerId'], _INTEGER);
                $name = $this->validateParameter('name', $this->param['name'], _STRING, FALSE);
                $addr = $this->validateParameter('addr', $this->param['addr'], _STRING, FALSE);
                $mobile = $this->validateParameter('mobile', $this->param['mobile'], _INTEGER, FALSE);

                # seteo las propiedades de customers
                $cust = new Customer;
                $cust->setId($customerId);
                $cust->setName($name);
                $cust->setAddress($addr);
                $cust->setMobile($mobile);
                $cust->setUpdatedBy($this->userId);
                $cust->setUpdatedOn(date('Y-m-d'));

                # verifico si insero o no
                if (!$cust->update()){
                    $message = 'Failed to update';
                } else {
                    $message = 'Successfully Updated';
                }
            
            # si todo sale bien devuelvo success
            $this->returnResponse(SUCCESS_RESPONSE, $message);

            } catch (Exception $e){
                # devuelvo error en caso de falla en la DB
                $this->throwError(DATABASE_ERROR, $e->getMessage());
            }
        }

        public function deleteCustomer(){
            try{
                $customerId = $this->validateParameter('customerId', $this->param['customerId'], _INTEGER);

                $cust = new Customer;
                $cust->setId($customerId);
                if ($cust->delete()) {
                    $message = 'Successfully Deleted';
                } else {
                    $message = 'Failed to delete';
                }
                $this->returnResponse(SUCCESS_RESPONSE, $message);

            } catch (Exception $e){
                $this->throwError(DATABASE_ERROR, $e->getMessage());
            }
        }
    }
?>