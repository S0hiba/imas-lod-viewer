<?php
# -----------------------------
# アイドルマスター LOD viewer 一覧ビュー
# 2019.12.11 osirisP 初版作成
# -----------------------------


//Redisから一覧の取得を試みる
$dataListJson = $redis->get("viewer_list_{$endPointName}_{$rdfType}");
$dataList = json_decode($dataListJson, true);

//取得できなかった場合、SPARQLエンドポイントから取得
if (empty($dataList)) {
    //SPARQLクエリを生成
    $sparqlQueryAll = $prefixStr . $sparqlQuery;
    $sparqlQueryParam = urlencode($sparqlQueryAll);
    $requestUrl = "{$endPointUrl}?query={$sparqlQueryParam}&format=json";

    //SPARQLクエリをリクエストし、結果を取得
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $requestUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $sparqlResultJson = curl_exec($ch);
    $sparqlResultArrayAll = json_decode($sparqlResultJson, true);
    $sparqlResultArray = $sparqlResultArrayAll['results']['bindings'];

    //クエリの結果をRedisに保存
    $redis->set("viewer_query_result_{$endPointName}_{$rdfType}", $sparqlResultJson);

    //取得した結果を表示用の配列に整形
    if (isset($sparqlResultArray) && is_array($sparqlResultArray) && count($sparqlResultArray) > 0) {
        //表示用の配列を初期化
        $dataList = array();

        foreach ($sparqlResultArray as $sparqlResultRow) {
            //データ自体のタイプは自明な為、処理をスキップし次のループへ
            if ($sparqlResultRow['p']['value'] === 'http://www.w3.org/1999/02/22-rdf-syntax-ns#type') {
                continue;
            }

            //pの値を「#」で分解し、表示用配列のキーを取得
            $pSeparate = explode('#', $sparqlResultRow['p']['value']);
            $key = $pSeparate[1];

            //キーが取得できていない場合、「/」で分解して取得
            if (empty($key)) {
                $pSeparate = explode('/', $sparqlResultRow['p']['value']);
                $pSeparateLastKey = count($pSeparate) - 1;
                $key = $pSeparate[$pSeparateLastKey];
            }

            //oNameが存在するなら、oNameを値に使用する
            //そうでないなら、oの値をそのまま値に使用する
            $value = $sparqlResultRow['oName']['value'];
            if (empty($value)) {
                $value = $sparqlResultRow['o']['value'];
            }

            //表示用の配列にデータを追加
            if ($key === 'memberOf' || $key === 'owns') {
                //複数データを持つデータの場合、改行区切りの文字列として追加
                if (empty($dataList[$sparqlResultRow['resource']['value']][$key])) {
                    //未定義だった場合、空文字列で初期化
                    $dataList[$sparqlResultRow['resource']['value']][$key] = '';
                }
                $dataList[$sparqlResultRow['resource']['value']][$key] .= "{$value}\n";
            } else {
                $dataList[$sparqlResultRow['resource']['value']][$key] = $value;
            }
        }
    }

    //整形した表示用の配列をRedisに保存
    $dataListJson = json_encode($dataList);
    $redis->set("viewer_list_{$endPointName}_{$rdfType}", $dataListJson);
}

//smartyに変数をアサイン
$smarty->assign(array(
    'endPointName'  => $endPointName,
    'rdfType'       => strtolower($rdfType),
    'dataList'      => $dataList,
    'labelList'     => $labelList,
));

//list.htmlを表示
$smarty->display('../viewer/apps/list/list.html');

exit;
