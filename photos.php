<?php
define('API_ROOT','./');
require API_ROOT . 'global.php';

$request=$_SERVER['REQUEST_METHOD'];
/*---------------------------------upload-------------------------------------*/
if($request=='POST')	{		
$id = isset($_GET['id'])?$_GET['id']:"";
$title = isset($_POST['title'])? trim($_POST['title']):"";
$image = isset($_POST['image'])? trim($_POST['image']):"";
$description = isset($_POST['description']) ? trim($_POST['description']):"";
$tags = isset($_POST['tags'])? $_POST['tags']: "";
$token = isset($_POST['token']) ? trim($_POST['token']):"";
if(empty($image)){
	header('HTTP/1.1 400 Bad Request');
	echo json_encode();}
	else{
$pattern = '/data:image\/(gif|jpg|jpeg|png);base64/';
preg_match($pattern,$image,$matches);
$type = $matches[1];
$data = ($type=="jpeg") ? substr($image,23):substr($image,22);

$query = $dbh -> prepare('INSERT INTO image(title,img_type,description,token) VALUES (?,?,?,?)');
$query -> execute(array($title,$type,$description,$token));
$img_id = $dbh->lastInsertId();

$filename = str_pad($img_id,20,"0",STR_PAD_LEFT);
$filepath = API_ROOT .'/photos/'.$filename.'.' . $type;
file_put_contents($filepath,base64_decode($data));


	foreach ($tags as $t)
	{
		$query=$dbh->prepare('INSERT INTO tag(name) VALUES(?)');
		$query->execute(array($t));
		$tag_id = $dbh->lastInsertId();
		$q = $dbh->prepare('INSERT INTO tagmap(img_id,tag_id) VALUES (?,?)');
		$q->execute(array($img_id,$tag_id));
	}
	$url = 'http://huiyuanniebear.com/api/photos/' . str_pad($img_id,20,"0",STR_PAD_LEFT) . '.'.$type;
	$data = array(
	"title" => $title, 
	"url" => $url,
	"tags" => $tags,
	"description" => $description,
	"id" => $img_id,
);}
}
elseif($request=='GET') {
	if(isset($_GET['id'])) {/*---------------------------------view by id-------------------------------------*/
		$id = isset($_GET['id'])?$_GET['id']:"";
		$sql = 'SELECT title,img_type,description FROM image WHERE id = ?';
		$query = $dbh->prepare($sql);
		$query->execute(array($id));
		$res_img = $query->fetchAll();
		if(empty($res_img)) {
			header('HTTP/1.1 404 Not Found');
			echo json_encode();}
		else{
			$query = $dbh->prepare('SELECT t.name as tagname FROM tagmap as tm,tag as t'.
									  		' WHERE tm.img_id=? AND t.id=tm.tag_id');
			$query ->execute(array($id));
			$res_tags = $query -> fetchAll();
			if(empty($res_tags)) {
				header('HTTP/1.1 404 Not Found');
				echo json_encode();}
			$row = $query->rowCount($res_tags);
			$arr=array();			
			for($i=0;$i<$row;$i++){array_push($arr,$res_tags[$i][0]);}
			$url = 'http://huiyuanniebear.com/api/photos/' . str_pad($id,20,"0",STR_PAD_LEFT) . '.'.$res_img[0]['img_type'];
			$data = array(
							"title" => $res_img[0]['title'], 
							"url" => $url,
							"tags" => $arr,
							"description" => $res_img[0]['description'],
							"id" => $id,
			);
			header('HTTP/1.1 200 OK');
		}
	}
	else{/*---------------------------------view multiple-------------------------------------*/
		$start = $_GET['start'];
		$count = $_GET['count'];
		if(empty($start)){$start=0;}
		else{$start = intval($_GET['start']);}
		if(empty($count)){$count=20;}
		else{$count = intval($_GET['count']);}

		$query = $dbh->prepare('SELECT id,title,img_type,description FROM image LIMIT :start, :count');
		$query->bindParam(':start', $start, PDO::PARAM_INT);
		$query->bindParam(':count', $count, PDO::PARAM_INT);
		$query ->execute();

		$res = $query->fetchAll();
		$row = $query->rowCount($res);

		$images = array();
		for($i=0;$i<$row;$i++){
			$img_id = $res[$i][0];
			$sql = 'SELECT name as tagname FROM tagmap as tm, tag as t WHERE tm.img_id=? AND t.id=tm.tag_id';
			$query = $dbh->prepare($sql);
			$query ->execute(array($img_id));
			$tagres = $query->fetchAll();	
			$tagrow = $query->rowCount($res);
			$tags = array();
		
			for($j=0;$j<$tagrow;$j++){
				array_push($tags,$tagres[$j][0]);
			}
			$url = 'http://huiyuanniebear.com/api/photos/' . str_pad($img_id,20,"0",STR_PAD_LEFT) . '.'.$res[$i]['img_type'];
			$description = $res[$i]['description'];
			$images[$i] = array(
								"id" =>$img_id,
								"title" =>$res[$i]['title'],
								"url" => $url,
								"tags" => $tags,
								"description" => $description,							
							);
			}
			$query=$dbh->prepare('SELECT COUNT(*) FROM image');
			$query->execute();
			$r = $query->fetchAll();
			$count = $r[0][0];
			$data = array(
				"images"=>$images,
				"count" =>$count,);
			header('HTTP/1.1 200 OK');
		}
}		
elseif($request=='DELETE') {/*---------------------------------delete-------------------------------------*/
	//$_DELETE = array();
	parse_str(file_get_contents('php://input'),$_DELETE);
	$id = $_DELETE['id'];
	$token = $_DELETE['token'];
	if(empty($id)){header('HTTP/1.1 404 Not Found');}
	else{$id = intval($_DELETE['id']);}
	if(empty($token)){
		header('HTTP/1.1 400 Bad Request');
		echo json_encode();	
	}

	else{
		$query=$dbh->prepare('SELECT token FROM image WHERE id = ?');
		$query->execute(array($id));
		$r = $query->fetchAll();
		if(empty($r)){
			header('HTTP/1.1 403 Forbidden');
			echo json_encode();}
		elseif($r[0][0]==$token){
						
			$query=$dbh->prepare('SELECT img_type FROM image WHERE id = ?');
			$query->execute(array($id));
			$res = $query->fetchAll();
			$type = $res[0]['img_type'];
			$url = '/home/huiyuan/course/cs132/www/api/photos/'. str_pad($id,20,"0",STR_PAD_LEFT) .'.' . $type;
			unlink($url);
			$query = $dbh->prepare('SELECT DISTINCT tag_id FROM tagmap WHERE img_id = ?');
			$query ->execute(array($id));
			$r_id = $query->fetchAll();
			$row = $query->rowCount($r_id);
			
			$query=$dbh->prepare('DELETE FROM tagmap WHERE img_id = ?');
			$query->execute(array($id));
	
			$query=$dbh->prepare('DELETE FROM image WHERE id=?');
			$query->execute(array($id));

			$inQuery = implode(',', array_fill(0, $row, '?'));		
			
			$sql1 = 'DELETE FROM tag WHERE tag.id in ('.$inQuery.')';
			$q = $dbh->prepare($sql1);
			
			for($i=0;$i<$row;$i++){
				$q->bindValue(($i+1), $r_id[$i][0]);  
				}	

			$q->execute();		
			
			header('HTTP/1.1 204 No Content');
			$data = array(); 
		}
		else{
			header('HTTP/1.1 403 No Content');
			echo json_encode();
		}
	}
}
	
echo json_encode($data);
?>
	