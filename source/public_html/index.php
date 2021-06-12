<?php
# -----------------------------
# アイマス開発ドメイン マスターコントローラ
# 2019.04.21 osirisP 初版作成
# 2019.04.24 osirisP パス構造を見直し
# -----------------------------


//composer読み込み
require_once('../vendor/autoload.php');

//smartyをインスタンス化
$smarty = new Smarty();

//パスを初期化
$pathQuery = array();

//パスの文字エンコーディングと制御文字をチェック
$pathString = substr($_SERVER['REQUEST_URI'], 1, -1);
if (mb_check_encoding($pathString, 'UTF-8') && !preg_match('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', $pathString)) {
    //パスを取得
    $pathQuery = explode('/', $pathString);

    //パスの文字数をチェック
    if (isset($pathQuery) && is_array($pathQuery) && count($pathQuery) > 0) {
        foreach ($pathQuery as $tmpPath) {
            //英小文字と数字で1～10文字でないなら、パスを初期化
            if (!preg_match('/\A[a-z0-9]{1,13}\Z/ui', $tmpPath)) {
                $pathQuery = array();
            }
        }
    }
}

//パスに応じて処理を切り分ける
switch ($pathQuery[0]) {
    case 'viewer':
        include_once("/app/{$pathQuery[0]}/apps/controller.php");
        break;
    default:
        include_once('/app/viewer/apps/controller.php');
}

exit;
