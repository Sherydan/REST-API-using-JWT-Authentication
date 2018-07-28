<?php
    # SECURITY
    define('SECRET_KEY', 'test123');

    # data type
    define('_BOOLEAN', '1');
    define('_INTEGER', '2');
    define('_STRING',  '3');

    # ERROR CODES
    define('REQUEST_METHOD_NOT_VALID',      100);
    define('REQUEST_CONTENTTYPE_NOT_VALID', 101);
    define('REQUEST_NOT_VALID',             102);
    define('VALIDATE_PARAMETER_REQUIRED',   103);
    define('VALIDATE_PARAMETER_DATATYPE',   104);
    define('API_NAME_REQUIRED',             105);
    define('API_PARAM_REQUIRED',            106);
    define('API_DOESNT_EXISTS',             107);
    define('INVALID_USER_PASS',             108);
    define('USER_NOT_ACTIVE',               109);
    

    define('SUCCESS_RESPONSE',              200);

    # SERVER ERRORS
    define('AUTHORIZATION_HEADER_NOT_FOUND',300);
    define('ATHORIZATION_HEADER_NOT_FOUND',	301);
    define('ACCESS_TOKEN_ERRORS',			302);
    define('DATABASE_ERROR',                303);
    	
   
?>