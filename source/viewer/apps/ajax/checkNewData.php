<?php
# -----------------------------
# アイドルマスター LOD viewer LODデータ差分の非同期チェックスクリプト
# 2019.12.07 osirisP 初版作成
# -----------------------------


//リクエストから、チェック対象のエンドポイントとRDFタイプを取得
$postArray = $_POST;
switch ($postArray['endPointName']) {
    case 'imasparql':
        //エンドポイント名
        $endPointName = 'imasparql';
        //エンドポイントURL
        $endPointUrl = 'https://sparql.crssnky.xyz/spql/imas/query';
        //PREFIX
        $prefixStr = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>PREFIX rdfs:  <http://www.w3.org/2000/01/rdf-schema#>PREFIX imas: <https://sparql.crssnky.xyz/imasrdf/URIs/imas-schema.ttl#>PREFIX schema: <http://schema.org/>';
        //RDFタイプ
        $rdfType = 'Idol';
        //クエリ文字列
        $sparqlQuery = 'SELECT ?resource ?p ?pLabel ?o ?oName WHERE { ?resource rdf:type imas:Idol; ?p ?o. OPTIONAL {?o schema:name ?oName} } ORDER BY ?resource';
        //表示対象のデータ一覧
        $labelList = array(
            'name'              => '名前',
            'familyName'        => '姓',
            'givenName'         => '名',
            'nameKana'          => '名前よみがな',
            'familyNameKana'    => '姓よみがな',
            'givenNameKana'     => '名よみがな',
            'alternateName'     => '通称',
            'alternateNameKana' => '通称よみがな',
            'cv'                => '担当声優',
            'Title'             => '所属コンテンツ',
            'Type'              => 'タイプ',
            'Division'          => '区分',
            'Attribute'         => '属性',
            'Category'          => 'カテゴリ',
            'Color'             => 'イメージカラー',
            'gender'            => '性別',
            'age'               => '年齢',
            'birthPlace'        => '出身地',
            'birthDate'         => '誕生日',
            'Constellation'     => '星座',
            'BloodType'         => '血液型',
            'Handedness'        => '利き手',
            'height'            => '身長',
            'weight'            => '体重',
            'Bust'              => '胸囲',
            'Waist'             => '腹囲',
            'Hip'               => '臀囲',
            'ShoeSize'          => '靴のサイズ',
            'Hobby'             => '趣味',
            'Talent'            => '特技',
            'Favorite'          => '好きなもの',
            'memberOf'          => '所属ユニット',
            'owns'              => '所持衣装',
            'description'       => '紹介文',
        );

        break;
    case 'pikopla':
        //エンドポイント名
        $endPointName = 'pikopla';
        //エンドポイントURL
        $endPointUrl = 'https://mltd.pikopikopla.net/sparql';
        //PREFIX
        $prefixStr = 'BASE <https://mltd.pikopikopla.net/resource/>PREFIX mltd: <https://mltd.pikopikopla.net/mltd-schema#>PREFIX schema: <http://schema.org/>PREFIX dbo: <http://dbpedia.org/ontology/>PREFIX foaf: <http://xmlns.com/foaf/0.1/>PREFIX owl: <http://www.w3.org/2002/07/owl#>PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#>PREFIX xsd: <http://www.w3.org/2001/XMLSchema#>';
        //RDFタイプ
        $rdfType = 'Idol';
        //クエリ文字列
        $sparqlQuery = 'SELECT ?resource ?p ?pLabel ?o ?oName WHERE { ?resource rdf:type mltd:Idol; ?p ?o. OPTIONAL {?o schema:name ?oName} } ORDER BY ?resource';
        //表示対象のデータ一覧
        $labelList = array(
            'position'      => '並び順',
            'name'          => '名前',
            'cv'            => '担当声優',
            'memberOf'      => '所属',
            'typePrFaAn'    => '属性',
        );

        break;
    default:
        //リクエストが正しくないなら、400エラーを返して終了
        header('HTTP/1.0 400 Bad Request');
        exit;
}

//SPARQLクエリを生成
$sparqlQueryAll = $prefixStr . $sparqlQuery;
$sparqlQueryParam = urlencode($sparqlQueryAll);
$requestUrl = "{$endPointUrl}?query={$sparqlQueryParam}&format=json";

//SPARQLクエリをリクエストし、結果を取得
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $requestUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$sparqlResultJson = curl_exec($ch);

//Redisからクエリ結果のキャッシュを取得
$redisResultJson = $redis->get("viewer_query_result_{$endPointName}_{$rdfType}");

//SPARQLクエリの結果とRedis内のクエリ結果のキャッシュが同一の場合、
//データ差分チェック完了とし、レスポンスを返す
if ($sparqlResultJson === $redisResultJson) {
    header('HTTP/1.0 200 OK');
    print 'Query result is same as Redis data.';
    exit;
}

//SPARQLクエリの結果とRedis内のクエリ結果のキャッシュが異なる場合、
//クエリの結果から表示用配列を生成
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

header('HTTP/1.0 200 OK');
print 'Write new result of query to Redis.';
exit;
