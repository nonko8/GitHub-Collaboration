<?php

// 設定
require_once(dirname(__FILE__).'/config.php');

$header = getallheaders();
$post_data = file_get_contents( 'php://input' );
$hmac = hash_hmac('sha1', $post_data, $SECRET_KEY);
if ( isset($header['X-Hub-Signature']) && $header['X-Hub-Signature'] === 'sha1='.$hmac ) {
    $payload = json_decode($post_data, true);  // 受け取ったJSONデータ

    foreach ($COMMANDS as $branch => $command) {
      // ブランチ判断
      if($payload['ref'] == $branch){
        if($command !== ''){
          // コマンド実行
          exec($command);
          file_put_contents($LOG_FILE, date("[Y-m-d H:i:s]")." ".$_SERVER['REMOTE_ADDR']." ".$branch." ".$payload['commits'][0]['message']."\n", FILE_APPEND|LOCK_EX);
        }
      }
    }//foreach


} else {
    // 認証失敗
    file_put_contents($LOG_FILE_ERR, date("[Y-m-d H:i:s]")." invalid access: ".$_SERVER['REMOTE_ADDR']."\n", FILE_APPEND|LOCK_EX);
}

//http://qiita.com/oyas/items/1cbdc3e0ac35d4316885
//https://yosiakatsuki.net/blog/github-auto-deploy/
?>