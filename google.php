<?php
include 'config.php';

function sanitizeResult($string) {
	$string = html_entity_decode($string, ENT_QUOTES, "utf-8");
	$string = strip_tags($string);
	return $string;
}

function sanitizeSlackJSON($string) {
	return str_replace(array('<','>', '&'), array('\u003C', '\u003E', '\u0026'), $string);
}

$searchUrl = "http://ajax.googleapis.com/ajax/services/search/web?v=1.0&q=".rawurlencode($_POST['text']);
$searchData = json_decode(file_get_contents($searchUrl), true);
$searchData = $searchData['responseData']['results'];

$results = array();
foreach($searchData as $searchResult){
	array_push($results, array(
		'title' 		=> sanitizeResult($searchResult['title']),
		'title_link' 	=> urlEncode(sanitizeResult($searchResult['url'])),
		'text' 			=> sanitizeResult($searchResult['content']),
		'fallback'		=> sanitizeResult($searchResult['title']).': '.sanitizeResult($searchResult['content']),
		'color'			=> $config['attachmentStripeColor']
	));
}

$payload = array(
	'text' => '*Search for:* '.$_POST['text'],
    'attachments' 	=> $results,
    'username' 		=> $config['botName'],
    'icon_url' 		=> $config['botIconUrl'],
    'channel' 		=> $_POST['channel_id']
);

$jsonPayload = "payload=".sanitizeSlackJSON(json_encode($payload));

$curl = curl_init($config['incomingWebhookUrl']);
curl_setopt($curl, CURLOPT_POST, true);
curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonPayload);
curl_exec($curl);
curl_close($curl);
?>
