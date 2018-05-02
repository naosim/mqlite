<?php

class MessageEntity {
  private $id;
  private $type;
  private $message;
  private $createDateTime;

  function __construct(
    Id $id, 
    Type $type,
    Message $message,
    CreateDateTime $createDateTime
    ) {
    $this->id = $id;
    $this->type = $type;
    $this->message = $message;
    $this->createDateTime = $createDateTime;
  }

  function __get($name){
    if($name == 'id') {
      return $this->id;
    }
    if($name == 'type') {
      return $this->type;
    }
    if($name == 'message') {
      return $this->message;
    }
    if($name == 'createDateTime') {
      return $this->createDateTime;
    }
  }
}



class MessageEntityOption {
  private $valueOption;// Option<MessageEntity>
  function __construct(?MessageEntity $entity_nullable) {
    $this->valueOption = new Option($entity_nullable);
  }
  function map($func) {
    return $this->valueOption->map($func);
  }

  function forEach($func) {
    $this->valueOption->forEach($func);
    return $this;
  }
}

class Id extends StringVo {}
class Type extends StringVo {}
class Message extends StringVo {}

class CreateDateTime extends DateTimeVo {}

interface DateTimeFactory {
  function now():int;
  function requestTime():int;
}

interface IdFactory {
  function create(string $prefix):int;
}

interface Repository {
  function saveCreateEvent(MessageCreateEvent $event);
  function findAll();
}

class MessageCreateEvent {
  private $type;
  private $message;
  private $createDateTime;

  function __get($name){
    if($name == 'message') {
      return $this->message;
    }
    if($name == 'type') {
      return $this->type;
    }
    if($name == 'createDateTime') {
      return $this->createDateTime;
    }
  }

  function __construct(
    Type $type,
    Message $message,
    CreateDateTime $createDateTime
  ) {
    $this->type = $type;
    $this->message = $message;
    $this->createDateTime = $createDateTime;
  }
}

class Service {
  private $repository;
  function __construct(
    Repository $repository
  ){
    $this->repository = $repository;
  }

  function send(Type $type, Message $message, DateTimeFactory $dateTimeFactory) {
    $event = new MessageCreateEvent(
      $type,
      $message, 
      new CreateDateTime($dateTimeFactory->now())
    );
    $this->repository->saveCreateEvent($event);
  }

  function get(Type $type):MessageEntityOption {
    $entityOption = $this->repository->findOldest($type);
    return $entityOption
        ->forEach(function($v) {
          $this->repository->remove($v->id);
        });
  }

}