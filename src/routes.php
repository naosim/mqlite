<?php
use Slim\Http\Request;
use Slim\Http\Response;

$app->get('/api/send/{type}', function (Request $request, Response $response, array $args) {
  $type = new Type($args['type']);
  $message = new Message($request->getQueryParam('message'));

  $this->mqPdoFactory->getPDO(function($pdo) use($type, $message) {
    $r = new RepositoryImpl($pdo);
    $s = new Service($r);
    $s->send($type, $message, $this->dateTimeFactory);
  });

  return $this->responseFactory->ok($response);
});

$app->get('/api/get/{type}', function (Request $request, Response $response, array $args) {
  $type = new Type($args['type']);

  $result = $this->mqPdoFactory->getPDO(function($pdo) use($type) {
    $r = new RepositoryImpl($pdo);
    $s = new Service($r);
    $entityOption = $s->get($type);
    return $entityOption
      ->map(function($v){ return messageEntityToObj($v);})
      ->getOrElse(null);
  });
  
  // Render index view
  return $this->responseFactory->ok($response, $result);
});

$app->get('/api/createtable', function (Request $request, Response $response) {
  $result = $this->mqPdoFactory->getPDO(function($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS queue(
      id INTEGER PRIMARY KEY AUTOINCREMENT,
      type TEXT NOT NULL,
      message TEXT NOT NULL,
      create_datetime INTEGER NOT NULL
    )");
  });

  return $response->withJson(["msg" => "hello"]);
});

$app->get('/api/exception', function (Request $request, Response $response) {
  throw new RuntimeException('err');
});

$app->get('/', function (Request $request, Response $response) {
  return $this->renderer->render($response, 'index.phtml');
});

$app->get('/hoge', function (Request $request, Response $response) {
  $s = 'abcdef';
  var_dump(strpos($s, 'bd'));
  return "heelo";
});