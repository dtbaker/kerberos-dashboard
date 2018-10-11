<?php


$camera = $_GET['cam'];
$endpoint = $_GET['endpoint'];

$config = json_decode(file_get_contents('config.json'),true);

function curl_login($cam){
	$url = 'http://' . $cam['ip'] .':' . $cam['port'] . '/api/v1/login/login';
	$ch = curl_init($url);
	$cookie_file = "/tmp/cookie2-" .md5($cam['name']);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, [
		'username' => $cam['username'],
		'password' => $cam['password'],
	]);
	$response = curl_exec($ch);
	curl_close($ch);
	$cookie = file_get_contents($cookie_file);
}

function curl_api( $cam, $endpoint, $attempt = 0 ){

	$response = [];

	$url = 'http://' . $cam['ip'] .':' . $cam['port'] . $endpoint;
	$ch = curl_init($url);

	$cookie_file = "/tmp/cookie2-" .md5($cam['name']);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie_file);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie_file);

//	$header = [];
//	$header[] = 'Content-Type: application/json';
//	$header[] = 'Authorization: Basic ' . base64_encode($cam['username'].':'.$cam['password']);
//	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$data = curl_exec($ch);
	curl_close($ch);
	if( $attempt < 1 && strpos($data,'/login')!==false){
		curl_login($cam);
		return curl_api($cam, $endpoint, $attempt +1 );
	}
	$array = json_decode($data,true);
	if(is_array($array)){
		$response = $array;
	}
	return $response;

}

foreach($config['servers'] as $cam){
	if($cam['name'] == $camera){

		switch($endpoint){
			case 'get_recent':

				$days= curl_api( $cam, '/api/v1/images/days' );
				$events = [];
				$max_events = 10;
				foreach($days as $day){
					if(count($events) <= $max_events) {
						$day_hours = curl_api( $cam, '/api/v1/images/' . $day . '/hours' );
						foreach(array_reverse($day_hours) as $hour => $hour_events){
							if($hour_events > 0 ){
								$found_events = 0;
								$index = 1;
								while(count($events) <= $max_events && $found_events < $hour_events && $index < 12){
									$actual_events = curl_api( $cam, '/api/v1/images/'. $day .'/12/' . $index .'/' . $hour);
									foreach($actual_events as $actual_event){
										if(count($events) <= $max_events && $actual_event && !empty($actual_event['type']) && $actual_event['type'] == 'image'){
											$actual_event['day'] = $day;
											$events[] = $actual_event;
										}
									}
									$index++;
								}
							}
						}
					}

				}
				$response = $events;

				break;
			default:
				$response = curl_api( $cam, $endpoint );
				break;
		}


	}
}
header('Content-type: application/json');
echo json_encode($response);