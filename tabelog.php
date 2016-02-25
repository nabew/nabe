<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="content-language" content="ja">
<head>
</head>
<body>
<?php
// phpQueryの読み込み
require_once("phpQuery-onefile.php");

// HTMLデータを取得する
$HTMLData = file_get_contents('http://tabelog.com/tokyo/A1310/A131003/');

// HTMLをオブジェクトとして扱う
$phpQueryObj = phpQuery::newDocument($HTMLData);

/*
$i=0;
foreach($phpQueryObj['h5'] as $val) {
  // pq()メソッドでオブジェクトとして再設定しつつさらに下ってhrefを取得
  $title_list[$i] = pq($val)->find('a')->text();
  $i++;
}
*/
$i=0;
foreach ($phpQueryObj[".list-rst__rst-name-target"] as $li){
  $tenpo_name[$i] =  pq($li)->text(); // a要素の中のテキストを取得して表示
  $tenpo_url[$i] = pq($li)->attr('href');
  //$tenpo_name[$j] =  pq($li)->find('a')->text();

  $i++;
}

//取得店舗数設定
$list_num = 10;

//店舗データ取得ここから-----------------
$file_path = "tenpo_data.csv";

$file = fopen( $file_path, "w" ); 
$export_csv_title = array( "店舗ID", "店舗名", "ジャンル", "電話番号", "住所", "アクセス", "営業時間", "定休日", "予算（お店）", "予算（ユーザー）", "カード", "サービス料", "席数", "個室", "貸切", "禁煙", "駐車場", "空間", "携帯", "飲み放題メニュー", "コース", "ドリンク", "料理", "こんな時にオススメ", "ロケーション", "サービス", "お子様", "ホームページ", "オープン日" );
foreach( $export_csv_title as $key => $val ){             
  $export_header[] = mb_convert_encoding($val, 'SJIS-win', 'UTF-8');
}
$return = fputcsv($file, $export_header);

for($j=0;$j<$list_num;$j++){
  $HTMLData = file_get_contents($tenpo_url[$j]);
  $phpQueryObj = phpQuery::newDocument($HTMLData);

  $k=1;
  $data[0] = $j+1;
  foreach ($phpQueryObj[".rst-data"]->find('td') as $li2) {
      $data[$k] = pq($li2)->text();
      $k++;
  }
  foreach( $data as $key2 => $val2 ){             
    $val2 = preg_replace("/( |　)/", "", $val2 );
    $list[] = mb_convert_encoding($val2, 'SJIS-win', 'UTF-8');
  }
  fputcsv($file, $list);
  $list = "";
}
fclose($file);
$export_header = "";
$data = "";
//店舗データ取得ここまで-----------------

//口コミデータ取得ここから-----------------
$file_path = "kutikomi_data.csv";

$file = fopen( $file_path, "w" ); 
$export_csv_title = array( "店舗ID", "タイトル", "投稿者", "属性", "口コミ" );
foreach( $export_csv_title as $key => $val ){             
    $export_header[] = mb_convert_encoding($val, 'SJIS-win', 'UTF-8');
}
$return = fputcsv($file, $export_header);

for($j=0;$j<$list_num;$j++){
  $kutikomi_url = $tenpo_url[$j]."dtlrvwlst/";

  $HTMLData = file_get_contents($kutikomi_url);
  $phpQueryObj = phpQuery::newDocument($HTMLData);

  $i=0;
  $kutikomi[$i][tenpo_id] = $j+1;
  foreach ($phpQueryObj[".rvw-item__rvw-title"] as $li) {
    if($i<5){
      $kutikomi[$i][title] = pq($li)->text();
      $i++;
    }
  }
  $i=0;
  foreach ($phpQueryObj[".rvw-item__rvwr-data"] as $li) {
    if($i<5){
      $kutikomi[$i][name] = pq($li)->text();
      $i++;
    }
  }
  $i=0;
  foreach ($phpQueryObj[".rvw-item__rvwr-profile"] as $li) {
    if($i<5){
      $kutikomi[$i][zoksei] = pq($li)->text();
      $i++;
    }
  }
  $i=0;
  foreach ($phpQueryObj[".rvw-item__contents"] as $li) {
    if($i<5){
      $kutikomi[$i][kutikomi] = pq($li)->text();
      $i++;
    }
  }
  foreach( $kutikomi as $key2 => $val2 ){
      foreach ($val2 as $key3 => $value) {
          $value = preg_replace("/( |　)/", "", $value );
          $list[] = mb_convert_encoding($value, 'SJIS-win', 'UTF-8');
      }
  }   
  $return = fputcsv($file, $list);
  $list = "";
}
fclose($file);
$export_header = "";
$data = "";
//口コミデータ取得ここまで-----------------

//画像データ取得ここから-----------------
$file_path = "photo_data.csv";
$file = fopen( $file_path, "w" ); 
$export_csv_title = array( "店舗ID", "カテゴリー", "画像URL" );
foreach( $export_csv_title as $key => $val ){             
    $export_header[] = mb_convert_encoding($val, 'SJIS-win', 'UTF-8');
}
$return = fputcsv($file, $export_header);

for($j=0;$j<$list_num;$j++){
  $photo_url = $tenpo_url[$j]."dtlphotolst/1/smp2/";
  $HTMLData = file_get_contents($photo_url);
  $phpQueryObj = phpQuery::newDocument($HTMLData);

  $data[0] = $j+1;
  $data[1] = "料理";
  $i=0;
  foreach($phpQueryObj[".thum-photobox__img"]->find('img') as $li2) {
    if($i<10){
        $data[2] = $li2->getAttribute('src');
        foreach( $data as $key2 => $val2 ){             
            $val2 = preg_replace("/( |　)/", "", $val2 );
            $val2 = preg_replace("/(150x150_square_)/", "", $val2 );
            $list[] = mb_convert_encoding($val2, 'SJIS-win', 'UTF-8');
        }
        $return = fputcsv($file, $list);
        $list = "";
    }
      $i++;
  }

  $photo_url = $tenpo_url[$j]."dtlphotolst/7/smp2/";
  $HTMLData = file_get_contents($photo_url);
  $phpQueryObj = phpQuery::newDocument($HTMLData);

  $data[0] = $j+1;
  $data[1] = "ドリンク";
  $i=0;
  foreach($phpQueryObj[".thum-photobox__img"]->find('img') as $li2) {
    if($i<10){
        $data[2] = $li2->getAttribute('src');
        foreach( $data as $key2 => $val2 ){             
            $val2 = preg_replace("/( |　)/", "", $val2 );
            $val2 = preg_replace("/(150x150_square_)/", "", $val2 );
            $list[] = mb_convert_encoding($val2, 'SJIS-win', 'UTF-8');
        }
        $return = fputcsv($file, $list);
        $list = "";
    }
    $i++;
  }

  $photo_url = $tenpo_url[$j]."dtlphotolst/3/smp2/";
  $HTMLData = file_get_contents($photo_url);
  $phpQueryObj = phpQuery::newDocument($HTMLData);

  $data[0] = $j+1;
  $data[1] = "内観";
  $i=0;
  foreach($phpQueryObj[".thum-photobox__img"]->find('img') as $li2) {
    if($i<3){
        $data[2] = $li2->getAttribute('src');
        foreach( $data as $key2 => $val2 ){             
            $val2 = preg_replace("/( |　)/", "", $val2 );
            $val2 = preg_replace("/(150x150_square_)/", "", $val2 );
            $list[] = mb_convert_encoding($val2, 'SJIS-win', 'UTF-8');
        }
        $return = fputcsv($file, $list);
        $list = "";
    }
    $i++;
  }

  $photo_url = $tenpo_url[$j]."dtlphotolst/4/smp2/";
  $HTMLData = file_get_contents($photo_url);
  $phpQueryObj = phpQuery::newDocument($HTMLData);

  $data[0] = $j+1;
  $data[1] = "外観";
  $i=0;
  foreach($phpQueryObj[".thum-photobox__img"]->find('img') as $li2) {
    if($i<5){
        $data[2] = $li2->getAttribute('src');
        foreach( $data as $key2 => $val2 ){             
            $val2 = preg_replace("/( |　)/", "", $val2 );
            $val2 = preg_replace("/(150x150_square_)/", "", $val2 );
            $list[] = mb_convert_encoding($val2, 'SJIS-win', 'UTF-8');
        }
        $return = fputcsv($file, $list);
        $list = "";
    }
    $i++;
  }
}
fclose($file);
//画像データ取得ここまで-----------------

//------------------
/*
$k=0;
foreach ($phpQueryObj[".school_tel"] as $tel){
  $tel_list[$k] =  pq($tel)->text() . "<br>"; // a要素の中のテキストを取得して表示
  $k++;
}
*/

/*
for($m=0;$m<20;$m++){
	echo "店名：".$tenpo_name[$m]."<br />";
  echo "URL：".$tenpo_url[$m]."<br /><br />";
}
*/
?>
</body>
</html>