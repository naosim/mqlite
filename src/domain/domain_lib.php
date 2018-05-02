<?php
class Option {
  private $value;
  function __construct($value) {
    $this->value = $value;
  }
  function map($func):Option {
    if($this->isEmpty()) {
      return $this;
    }
    return new Option($func($this->value));
  }
  function forEach($func) {
    if($this->isEmpty()) {
      return $this;
    }
    $func($this->value);
    return $this;
  }
  function isEmpty():bool {
    return $this->value === null;
  }
  function isDefined():bool {
    return !$this->isEmpty();
  }
  function get() {
    if($this->isEmpty()) {
      throw new RuntimeException('value is null');
    }
    return $this->value;
  }
  function getOrElse($defaultvalue) {
    if($this->isEmpty()) {
      return $defaultvalue;
    }
    return $this->value;
  }
}

class StringVo {
  private $value;
  function __construct(string $value) {
    $this->value = $value;
  }
  function __get($name){
    if($name == 'value') {
      return $this->value;
    }
  }
}

class DateTimeVo {
  private $value;
  function __construct(int $value) {
    $this->value = $value;
  }
  function __get($name){
    if($name == 'value') {
      return $this->value;
    }
  }
}