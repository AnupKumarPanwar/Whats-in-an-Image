<?php









require('ftpInfo.php');



// Create connection
$conn = mysqli_connect($dbServer, $dbUsername, $dbPassword, $dbName);
// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}


$tempname=rand(1000000000, 9999999999999);




// $userId=mysqli_real_escape_string($conn, $_POST['userId']);
// $tags=mysqli_real_escape_string($conn, $_POST['tags']);
// $caption=mysqli_real_escape_string($conn, $_POST['caption']);

$target_dir="../uploads/";
$t=time();
$target_file_name=basename($t.$_FILES["fileToUpload"]["name"]);

$target_file_name = preg_replace('/\s+/', '_', $target_file_name);

$target_file=$target_dir.$tempname.basename($t.$_FILES["fileToUpload"]["name"]);
$target_file= preg_replace('/\s+/', '_', $target_file);
//$uploadOk=1; // anup ne yha pe glati kar di ... agar ye abhi 1 hai to fir success:2 print hoga ...
$uploadOk=1;







$info=getimagesize($_FILES["fileToUpload"]["tmp_name"]);
if ($info===FALSE) {
	die('{"result":[{"success" : "0", "data" : "Uploaded File is NOT an image"}]}');
}





$imageFileType=pathinfo($target_file, PATHINFO_EXTENSION);

if (isset($_POST["submit"])) {


	if ($imageFileType=="jpg" || $imageFileType=="jpeg" || $imageFileType=="png"  || $imageFileType=="bmp"  ) {
		// echo "File is an image - ".$check["mime"].".";
		$uploadOk=1;
	}
	else{
		die ('{"result":[{"success" : "0", "data" : "Uploaded File is NOT an image"}]}');
		$uploadOk=0;
	}
}




$tags=" ";








function make_thumb($src, $dest)
{
	$imageFileType=pathinfo($src, PATHINFO_EXTENSION);

	if ($imageFileType=="jpg" || $imageFileType=="jpeg"  )
	{
		$source_image=imagecreatefromjpeg($src);
	}
	else if ($imageFileType=="png")
	{
		$source_image=imagecreatefrompng($src);
	}
	else if ($imageFileType=="bmp")
	{
		$source_image=imagecreatefromwbmp($src);
	}

	$width=imagesx($source_image);
	$height=imagesy($source_image);

	$desired_width=800;
	// $desired_width=floor($width/3);

	$desired_height=($desired_width*$height/$width);

	$virtual_image=imagecreatetruecolor($desired_width, $desired_height);

	imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);

	imagejpeg($virtual_image, $dest, 80);






	$url = 'https://api.clarifai.com/v1/token/';
	$data = array('grant_type' => 'client_credentials',
		'client_id' => 'R5uIa5cJDMNfIc266TmC2ElDbgtoVSSqOFZUPJvv',
		'client_secret' => 'lOFtjtdZnoBYzAUrfBvdUlkzZ3yQGPmKU7TFtFYo'
		);

	// use key 'http' even if you send the request to https://...
	$options = array(
	    'http' => array(
	        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n".
	        				"Authorization : Bearer tD0qUbgqOSH6CwVTKwi9IJPVvYQTV8",
	        'method'  => 'POST',
	        'content' => http_build_query($data, PHP_QUERY_RFC1738)
	    )
	);
	$context  = stream_context_create($options);
	$result =json_decode(file_get_contents($url, false, $context));


	$accessToken=$result->access_token;








	$url = 'https://api.clarifai.com/v1/tag/';
	$data = array('url' => 'http://hashbnm.16.com/thumb/'.$dest);

	// use key 'http' even if you send the request to https://...
	$options = array(
	    'http' => array(
	        'header'  => "Content-Type: application/x-www-form-urlencoded\r\n".
	        				"Authorization : Bearer ".$accessToken,
	        'method'  => 'POST',
	        'content' => http_build_query($data, PHP_QUERY_RFC1738)
	    )
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);
	if ($result === FALSE) { /* Handle error */ }


	$response=json_decode($result);





	// print_r($response);
	for ($i=0; $i <count($response->results[0]->result->tag->classes) ; $i++) {
			$GLOBALS['tags'].=" ".$response->results[0]->result->tag->classes[$i];
			// echo($response->results[0]->result->tag->classes[$i]);
	}





}
















if ($uploadOk==1) {
	if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {
	make_thumb($target_file, "../thumb/".$target_file_name);





$sql = "INSERT INTO uploadedfileslist (imageUrl, originalUrl, uploadedAt, tags)
VALUES ('$target_file_name', '$tempname.$target_file_name', now(), '$tags')";





$duck=null;
						$du=null;
						$dudu=null;
						// print_r(array_keys($r));
						// print_r($r->query->pages->);
						$dada=null;
						$arrayindex=null;



		if (mysqli_query($conn, $sql))
		{



				$tagsArray=explode(' ',$GLOBALS['tags']);

				$similarPostsArray=array();
				for ($i=0; $i <count($tagsArray); $i++)
				{

					$currentTag=$tagsArray[$i];
					// echo $currentTag.'\n';
					$similarPosts="SELECT nameofobject, imageUrl, tags FROM uploadedfileslist WHERE tags LIKE '%$currentTag%'";
					$result1=mysqli_query($conn, $similarPosts);
					while ($r1=mysqli_fetch_assoc($result1))
					{
						$similarPostsArray[]=$r1;
					}
					// print_r($similarPostsArray);
				}



				$tempSimilarPostsArray=array();
				for($i=0; $i<count($similarPostsArray); $i++)
				{
					$count=0;
					for($j=$i; $j<count($similarPostsArray); $j++)
					{
						if($similarPostsArray[$j]['imageUrl']==$similarPostsArray[$i]['imageUrl'])
						{
							$count++;
						}
					}

					if($count>=3)
					{
						$similarPostsArray[$i]['similariry']=round($count*100/count($tagsArray));
						if($similarPostsArray[$i]['similariry']>=50)
						{
							$tempSimilarPostsArray[]=$similarPostsArray[$i];
						}
					}
				}

				$similarPostsArrayUnique=array_unique($tempSimilarPostsArray, SORT_REGULAR);



				for ($i=0; $i <count($similarPostsArrayUnique) ; $i++) { 
					for ($j=0; $j <count($similarPostsArrayUnique)-1 ; $j++) { 
						if ($similarPostsArrayUnique[$j]['similariry']<$similarPostsArrayUnique[$j+1]['similariry']) {
							$temp=$similarPostsArrayUnique[$j];
							$similarPostsArrayUnique[$j]=$similarPostsArrayUnique[$j+1];
							$similarPostsArrayUnique[$j+1]=$temp;
						}
					}
				}


				$similarPostsArrayUniqueFinal=array();
				$similarPostsArrayUniqueFinal[]=$similarPostsArrayUnique[0];

				for ($i=1; $i <count($similarPostsArrayUnique) ; $i++) { 
					$flag=0;
					for ($j=0; $j <count($similarPostsArrayUniqueFinal) ; $j++) { 
						if($similarPostsArrayUnique[$i]['imageUrl']==$similarPostsArrayUniqueFinal[$j]['imageUrl'])
						{
							$flag=1;
						}
					}
					if ($flag==0) {
						$similarPostsArrayUniqueFinal[]=$similarPostsArrayUnique[$i];
					}
				}



				for ($i=0; $i <count($similarPostsArrayUniqueFinal) ; $i++) { 
					if($similarPostsArrayUniqueFinal[$i]['nameofobject']!=null && $similarPostsArrayUniqueFinal[$i]['nameofobject']!="")
					{
						$fuddu=$similarPostsArrayUniqueFinal[$i]['nameofobject'];
						$duck=file_get_contents("https://en.wikipedia.org/w/api.php?format=json&action=query&prop=extracts&exintro=&explaintext=&titles=$fuddu");
						$du=json_decode($duck);
						$dudu=$du->query->pages;
						// print_r(array_keys($r));
						// print_r($r->query->pages->);
						$dada="duck";

					

						foreach ($dudu as $key => $value) {
							// $count+=1;
							// echo $count;
							// echo $key;
							$dada = $key;
							// $id=($key);
						}
							$arrayindex=$i;

						// print_r($id);
						// print_r($x->$id->extract);
						break;
					}

				}


				$done=str_replace("\n", "\\n", $dudu->$dada->extract);
				$done=str_replace("\"", " ", $done);

				// $done=addslashes($done);


		  		echo '{"result":[{"success" : "1", "nameofobject":"'.$similarPostsArrayUniqueFinal[$arrayindex]['nameofobject'].'", "imageUrl":"'.$similarPostsArrayUniqueFinal[$arrayindex]['imageUrl'].'" , "description" : "'.$done.'"}]}';
		}
		else
		{
		    echo '{"result":[{"success" : "2", "data":"Image Upload Failed 1"}]}';
		    // echo($mysqli_error($conn));
		}

	}

	else{
		echo '{"result":[{"success" : "2", "data":"Image Upload Failed 2"}]}';
	}
}











mysqli_close($conn);








?>
