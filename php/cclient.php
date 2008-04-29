<html>
<head><title>An actual page for testing</title>
</head>
<body>
<h1>Testing a miniature http client setup thing</h1>
<?php


$url = 'localhost/tag.php?folksotagid=16';

$headers = array(
                 "GET /tag.php HTTP/1.0",
                 "Content-Type: application/x-www-form-urlencoded",
                 "Content-length: 0"
                 );

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADERS, $headers);
//curl_setopt($ch, CURLOPT_GETFIELDS, 'folksotagid=Personnage');
curl_exec($ch);

print curl_getinfo($ch, CURLINFO_HTTP_CODE);

?>
</body>