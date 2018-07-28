<?php
    $curl = curl_init();
    # passing the json directly
    $request = '{
                    "name":"generateToken",
                    "param":{
                            "email":"luis.rainmaker@gmail.com",
                            "pass":"123456"
                        }
                }';

    # create json from php array
    $user_email = 'luis.rainmaker@gmail.com';
    $user_pass = '123456';
    $user_array = [
        'name'=> 'generateToken',
        'param' => [
            'email' => $user_email,
            'pass' => $user_pass
            ]
    ];

    curl_setopt($curl, CURLOPT_URL, 'http://localhost/jwt_api/');
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($user_array));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
    $err = curl_error($curl);

    if ($err) {
        echo 'Curl Error: ' . $err;
    } else {
        $response = json_decode($result, true);
        $token = $response['response']['result']['token'];
        curl_close($curl);

        # llaamr otra api
		$curl = curl_init();
		$request = '{
						"name":"getCustomersDetails",
						"param":{
							"customerId":3
						}
					}';
			curl_setopt_array($curl, array(
			  CURLOPT_URL => "http://localhost/jwt_api/",
			  CURLOPT_RETURNTRANSFER => true,
			  CURLOPT_ENCODING => "",
			  CURLOPT_MAXREDIRS => 10,
			  CURLOPT_TIMEOUT => 30,
			  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			  CURLOPT_CUSTOMREQUEST => "POST",
			  CURLOPT_POSTFIELDS => $request,
			  CURLOPT_HTTPHEADER => array(
				    "authorization: Bearer $token",
				    "content-type: application/json",
				  ),
			));
			$response = curl_exec($curl);
			$err = curl_error($curl);
			if ($err) {
			  echo "cURL Error #:" . $err;
			} else {
			  echo $response;
			}
			curl_close($curl);
    }
   
?>
