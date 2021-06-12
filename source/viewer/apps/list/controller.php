<?php
# -----------------------------
# アイドルマスター LOD viewer 一覧コントローラ
# 2019.12.11 osirisP 初版作成
# -----------------------------


//パスに応じて変数の値を設定
switch ($pathQuery[2]) {
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
}

//ビューを読み込む
include_once('/app/viewer/apps/list/view.php');

exit;
