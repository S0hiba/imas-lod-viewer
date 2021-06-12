<?php
# -----------------------------
# アイドルマスター LOD viewer Ajaxコントローラ
# 2019.12.07 osirisP 初版作成
# -----------------------------


//パスに応じて処理を切り分ける
switch ($pathQuery[2]) {
    case 'checkNewData':
        include_once("/app/viewer/apps/ajax/{$pathQuery[2]}.php");
        break;
    default:
        //パスが正しくないなら、404エラーを返して終了
        header('HTTP/1.0 404 Not Found');
        exit;
}

exit;
