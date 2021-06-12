<?php
# -----------------------------
# アイドルマスター LOD viewer マスターコントローラ
# 2019.04.24 osirisP 初版作成
# 2019.12.11 osirisP Redis導入とパス構造の見直し
# -----------------------------


//Redisへ接続
$redis = new Predis\Client(getenv('REDIS_URL'));

//パスに応じて処理を切り分ける
switch ($pathQuery[1]) {
    case 'ajax':
    case 'list':
        include_once("/app/viewer/apps/{$pathQuery[1]}/controller.php");
        break;
    default:
        include_once('/app/viewer/apps/list/controller.php');
}

exit;
