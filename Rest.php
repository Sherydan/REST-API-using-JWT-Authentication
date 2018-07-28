<?php
require_once('constants.php');
    class Rest{
        protected $request;
        protected $serviceName;
        protected $param;
        protected $dbConn;
		protected $userId;

        public function __construct(){

            # solo las peticiones post a la api seran procesadas
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->throwError(REQUEST_METHOD_NOT_VALID, 'Request method is not valid');
            } else {
                # si la peticion es post, tomo el contenido y lo valido
                $handle = fopen('php://input', 'r');
                $this->request = stream_get_contents($handle);
                $this->validateRequest();

                $db = new DbConnect;
                $this->dbConn = $db->connect();

                # si el nombre de la peticion NO es generar token 
                # entonces debo validar peticion
                # de esa manera, todas las peticiones que no sean generar token, pediral token JWT para utilizarlas
                if ('generatetoken' != strtolower($this->serviceName)) {
                    $this->validateToken();
                }
            }
        }

        public function validateRequest(){

            # en el header, debe especificarse que el conetnido es application/json
            if ($_SERVER['CONTENT_TYPE'] !== 'application/json') {
                $this->throwError(REQUEST_CONTENTTYPE_NOT_VALID, 'Request content type is not valid');
            } else {
                # true para que se decodee en un arreglo asociativo
                $data = json_decode($this->request, true);
            }
            
            # para utilizar la api, debo entregar si o si un nombre de la api a utilizar
            if (!isset($data['name']) || $data['name'] == '') {
                $this->throwError(API_NAME_REQUIRED, 'Api name is required');

            } else {
                $this->serviceName = $data['name'];
            }
            
            # deben haber datos en param
            if (!is_array($data['param'])) {
                $this->throwError(API_PARAM_REQUIRED, 'Api param is required');

            } else {
                $this->param = $data['param'];
            }
        }


        public function processApi(){
            $api = new API;
            if (!method_exists($api, $this->serviceName)) {
                $this->throwError(API_DOESNT_EXISTS, "API does not exists.");
            } else {
                $rMethod = new reflectionMethod('Api', $this->serviceName);
                $rMethod->invoke($api);
            }
        }

        public function validateParameter($fieldName, $value, $dataType, $required = TRUE){
            if ($required == true && empty($value) == true) {
                $this->throwError(VALIDATE_PARAMETER_REQUIRED, $fieldName ." param is required");
            } elseif ($required == true && !empty($value) ) {
                switch ($dataType) {
                    case _BOOLEAN:
                        if (!is_bool($value)) {
                            $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for ". $fieldName . " it should be Boolean");
                        }
                        break;
    
                    case _INTEGER:
                        if (!is_numeric($value)) {
                            $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for ". $fieldName . " it should be Integer");
                        }
                        break;
    
                    case _STRING:
                        if (!is_string($value)) {
                            $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for ". $fieldName . " it should be String");
                        }
                        break;
                    
                    default:
                    $this->throwError(VALIDATE_PARAMETER_DATATYPE, "Datatype is not valid for ". $fieldName);
                        break;
                }
            }

            return $value;
            
        }

        # funcion para devolver errores
        public function throwError($code, $message){
            header('Content-Type: application/json');
            $errorMsg = json_encode(['error' =>['status' => $code, 'message' => $message]], JSON_PRETTY_PRINT);
            echo $errorMsg; exit;
        }

        # funcion para devovler datos
        public function returnResponse($code, $data){
            header('Content-Type: application/json');
            $response = json_encode(['response' => ['status' => $code, "result" => $data]], JSON_PRETTY_PRINT);
            echo $response; exit;
        }

        # removi la parte de validar token en el addCustomer (Api.php)
        # y la incluyo en rest, para asi validar TODAS las request
        public function validateToken() {
            try{
                $token = $this->getBearerToken();
                $payload = JWT::decode($token, SECRET_KEY, ['HS256']);

                $stmt = $this->dbConn->prepare("SELECT * FROM users WHERE id = :userid");
                $stmt->bindParam(':userid', $payload->userID);
                $stmt->execute();
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                if (!is_array($user)) {
                    $this->returnResponse(INVALID_USER_PASS, "User not found");
                }

                if ($user['active'] == 0) {
                    $this->returnResponse(USER_NOT_ACTIVE, "This user may be deactivated. Please contact to admin");
                }
                $this->userId = $payload->userID;
            } catch (Exception $e){
                $this->throwError(ACCESS_TOKEN_ERRORS, $e->getMessage());
            }
		}



        /**
	    * Get header Authorization
	    * */
	    public function getAuthorizationHeader(){
	        $headers = null;
	        if (isset($_SERVER['Authorization'])) {
	            $headers = trim($_SERVER["Authorization"]);
	        }
	        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
	            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
	        } elseif (function_exists('apache_request_headers')) {
	            $requestHeaders = apache_request_headers();
	            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
	            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
	            if (isset($requestHeaders['Authorization'])) {
	                $headers = trim($requestHeaders['Authorization']);
	            }
	        }
	        return $headers;
	    }
	    /**
	     * get access token from header
	     * */
	    public function getBearerToken() {
	        $headers = $this->getAuthorizationHeader();
	        // HEADER: Get the access token from the header
	        if (!empty($headers)) {
	            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
	                return $matches[1];
	            }
	        }
	        $this->throwError( ATHORIZATION_HEADER_NOT_FOUND, 'Access Token Not found');
	    }
    }
?>