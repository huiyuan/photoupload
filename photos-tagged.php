<?php
define('API_ROOT','./');
require API_ROOT . 'global.php';


$start = $_GET['start'];
$count = $_GET['count'];

if(empty($start)){$start=0;}
		
else{$start = intval($_GET['start']);}
		
if(empty($count)){$count=20;}
elseif($count>50){$count=50;}
	else{$count = intval($_GET['count']);}

$tags = isset($_GET['tags'])?$_GET['tags']:"";
$tagL=count($tags);
$inQuery = implode(',', array_fill(0, count($tags), '?'));

$sql='SELECT * FROM (SELECT DISTINCT image.id as id,image.title as title, image.description as description,image.img_type as type,tag.name '.
	  'FROM image,tagmap,tag WHERE image.id=tagmap.img_id AND tagmap.tag_id=tag.id AND tag.name IN('.$inQuery.')) AS alias '.
	  'GROUP BY id HAVING COUNT(id)=? LIMIT ?,?';

$query = $dbh->prepare($sql);

foreach ($tags as $k => $t){
    $query->bindValue(($k+1), $t);  
    }
$query->bindValue((count($tags)+1),$tagL);
$query->bindValue((count($tags)+2),$start,PDO::PARAM_INT);
$query->bindValue((count($tags)+3),$count,PDO::PARAM_INT);
$query->execute();

$res = $query->fetchAll();
$row = $query->rowCount($res);

$images = array();
for($i=0;$i<$row;$i++){
	$img_id = $res[$i]['id'];
	$img_type = $res[$i]['type'];
	$title = $res[$i]['title'];
	$tags = array();
	$sql = 'SELECT name as tagname FROM tagmap as tm, tag as t WHERE tm.img_id=? AND t.id=tm.tag_id';
	$query = $dbh->prepare($sql);
	$query ->execute(array($img_id));
	$tagres = $query->fetchAll();	
	$tagrow = $query->rowCount($res);
	for($j=0;$j<$tagrow;$j++){
				array_push($tags,$tagres[$j][0]);
		}
	$url = 'http://huiyuanniebear.com/api/photos/' . str_pad($img_id,20,"0",STR_PAD_LEFT) . '.'.$img_type;
	$description = $res[$i]['description'];
	$images[$i] = array(
								"id" =>$img_id,
								"title" =>$title,
								"url" => $url,
								"tags" => $tags,
								"description" => $description,							
							);
}
$data = array(
	"images"=>$images,
	"count" => count($images),);
	header('HTTP/1.1 200 OK');
	
echo json_encode($data);
?>
