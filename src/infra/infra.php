<?php
class RepositoryImpl implements Repository {
  private $pdo;
  function __construct(
    PDO $pdo
  ){
    $this->pdo = $pdo;
  }

  function saveCreateEvent(MessageCreateEvent $event) {
    $stmt = $this->pdo->prepare('insert into queue (type, message, create_datetime) values (?, ?, ?)');
    $stmt->execute([$event->type->value, $event->message->value, $event->createDateTime->value]);
  }

  private function recordToEntity($obj) {
    return new MessageEntity(
      new Id($obj['id']),
      new Type($obj['type']),
      new Message($obj['message']),
      new CreateDateTime($obj['create_datetime'])
    );
  }

  function findOldest(Type $type):MessageEntityOption {
    $stmt = $this->pdo->prepare("SELECT * FROM queue WHERE type = ?");
    $stmt->execute([$type->value]);
    $list = $stmt->fetchAll();
    if(count($list) == 0) {
      return new MessageEntityOption(null);
    }
    return new MessageEntityOption($this->recordToEntity($list[0]));
  }

  function remove(Id $id) {
    $stmt = $this->pdo->prepare("DELETE FROM queue WHERE id = ?");
    $stmt->execute([$id->value]);
    $stmt->fetchAll();
  }

  function findAll() {
    $stmt = $this->pdo->prepare("SELECT * FROM queue");
    $stmt->execute();
    return $stmt->fetchAll();
  }
}

class SQLitePDOFactory {
  private $dbFileName;
  private $lockFileName;

  function __construct(
    string $dbFileName, 
    string $lockFileName
  ){
    $this->dbFileName = $dbFileName;
    $this->lockFileName = $lockFileName;
  }

  function getPDOWithoutLock() {
    // 接続
    $pdo = new PDO('sqlite:' . $this->dbFileName);
  
    // SQL実行時にもエラーの代わりに例外を投げるように設定
    // (毎回if文を書く必要がなくなる)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  
    // デフォルトのフェッチモードを連想配列形式に設定 
    // (毎回PDO::FETCH_ASSOCを指定する必要が無くなる)
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
  }

  function getPDO($funcWithPDO) {
    return $this->lock(function() use($funcWithPDO) {
      $pdo = $this->getPDOWithoutLock();
      return $funcWithPDO($pdo);
    });
  }

  function lock($fuc) {
    if(!file_exists($this->lockFileName)) {
      file_put_contents($this->lockFileName, '');
    }
    $lock_fp = fopen($this->lockFileName ,"w");
    flock($lock_fp, LOCK_EX);
    try {
      return $fuc();
    } finally {
      fclose($lock_fp);
    }
  }
}

class DateTimeFactoryImpl implements DateTimeFactory {
  function now():int {
    return floor(microtime(true) * 1000);
  }
  function requestTime():int {
    return (integer)floor($_SERVER['REQUEST_TIME_FLOAT'] * 1000);
  }
}

function messageEntityToObj(MessageEntity $entity) {
  return [
    'id'=>$entity->id->value,
    'type'=>$entity->type->value,
    'message'=>$entity->message->value,
    'create_datetime'=>$entity->createDateTime->value
  ];
}

class ResponseFactory {
  function ok($response, $obj = 'ok') {
    $result = [
      'status'=>['status_code'=>200, 'message'=>'ok'],
      'result'=>$obj
    ];
    return $response->withJson($result);
  }
  function ng($response, $exception) {
    // var_dump($exception);
    $result = [
      'status'=>['status_code'=>500, 'message'=>'ng'],
      'error'=>['class'=>get_class($exception), 'message'=>$exception->getMessage()]
    ];
    return $response->withJson($result, 500);
  }
}