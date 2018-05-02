# MQLite

SQLiteを使ったMessageQueueです

## Setup

### pear install

### access_token file
アクセス制限をかけたい場合は、`/public/access_token.txt`を作り、その中に任意のアクセストークンを保存する

### deploy
FTPサーバ等にアップロードする

### DB setup
テーブルを生成する
```
/public/api/createtable?access_token=[access_token]
```

## Usage
### GET /api/send/{type}?message=[message]&access_token=[access_token]
### GET /api/get/{type}?access_token=[access_token]