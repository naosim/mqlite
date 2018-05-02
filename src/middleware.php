<?php
// Application middleware

// log
$app->add(function ($request, $response, $next) {
  $this->logger->info($request->getUri()->getPath());
	$response = $next($request, $response);
	return $response;
});

// auth
$app->add(function ($request, $response, $next) {
  $accessTokenFile = 'access_token.txt';
  if(strpos($request->getUri()->getPath(), 'api') === false || !file_exists($accessTokenFile)) {
    return $next($request, $response);
  }

  $accessToken = file_get_contents($accessTokenFile);

  $list = [
    $request->getHeader('access_token'),
    $request->getQueryParam('access_token')
  ];
  $t = null;
  foreach($list as $value) {
    if($value != null) {
      $t = $value;
    }
  }
  if($t == null) {
    throw new RuntimeException('access_token required');
  } else if($t != $accessToken) {
    throw new RuntimeException('unmatch access_token');
  }
	$response = $next($request, $response);
	return $response;
});