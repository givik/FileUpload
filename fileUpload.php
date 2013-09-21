<?

/********** FILE UPLOAD FUNCTION ***********/
function FileUpload($path = "", $dir = "", $uniq = false, $size = "", $types = "",
						$images = false,
						$audio = false, 
						$video = false,
						$archives = false, 
						$other = false){
	
	// REWRITE GLOBAL ARRAY
	$files = $_FILES;
	
	// ARGUMENT INSURANCE
	if(!$files)
		exit("Incorrect First Parameter At Uploading" . "<br>");

	// ARGUMENT INSURANCE
	if(!is_dir(UPLDIR . $path))
		exit("Incorrect Path: <strong>" . trim($path) . "</strong><br>");
		
	// ARGUMENT INSURANCE	
	if(!$types&& !$images && !$audio && !$video && !$archives && !$other)
		exit("Incorrect Array Type Parameters At Uploading" . "<br>");
		
	// MAKE A STRING LOWERCASE
	$types = strtolower($types);
	
	// MAKE FULL PATH
	$path = UPLDIR . $path . "/";
	
	// ARRAY OF THE FILE FORMATS AND MIME TYPES 
	$mimeTypes = array(
		"images" =>
		   array('png' => 'image/png',
				'jpeg' => 'image/jpeg',
				'jpg' => 'image/jpeg',
				/*'gif' => 'image/gif',*/
				'bmp' => 'image/bmp'),
				
		"audio" =>
		   array('mp3' => 'audio/mpeg',
				'wav' => 'audio/wav',
				'mid' => 'audio/mid'),
		
		"video" =>
		   array('mov' => 'video/quicktime',
				'avi' => 'video/avi',
				'mpg' => 'video/mpeg',
				'wmv' => 'video/x-ms-wmv',
				'mp4' => 'video/mp4'),
					
		"archives" =>
		   array('zip' => 'application/zip',
				'rar' => 'application/octet-stream'),
		
		"other" =>
		   array('txt' => 'text/plain',
				'htm' => 'text/html',
				'html' => 'text/html',
				'css' => 'text/css',
				'xml' => 'text/xml'));

	// ARRAY OF THE ALLOWED FILE TYPES
	$Allowed = array();
		
	if($types && substr_count($types, ','))
	{
		// MAKE A ARRAY OF THE DIVIDED ELEMENTS OF A STRING
		$Allowed = explode(",", $types);
			
		// ONE-DIMENSIONAL ARRAY
		$all = array(); 
		
		// REMAKE ARRAY INTO ONE-DIMENSIONAL
		foreach($mimeTypes as $key => $val)
			foreach($mimeTypes[$key] as $k => $v)
				$all[$k] = $v;

		// CHECK SPECIFIED FILE TYPES AND MAKE NEW ARRAY
		for($i = 0; $i < count($Allowed); $i ++)
			if(!array_key_exists(trim($Allowed[$i]), $all))
				exit("Incorrect File Type: <strong>" . trim($Allowed[$i]) . "</strong><br>");
			else
				$arr[trim($Allowed[$i])] = $all[trim($Allowed[$i])];

		// REWRITE ARRAY
		$Allowed = $arr;
	}
	
	// IF SPECIFIED ONE FORMAT
	if($types && !substr_count($types, ','))
	{
		$type = "";
		
		foreach($mimeTypes as $key => $val)
			foreach($mimeTypes[$key] as $k => $v)
				if($k == trim($types))
					$type = $key;
					
		if(!$type)
			exit("Incorrect File Type: <strong>" . trim($types) . "</strong><br>");
			
		$Allowed[trim($types)] = $mimeTypes[$type][trim($types)];
	}

	// IF WE CHOOSE TYPES FROM ARRAY
	if(!$types)
	{
		// INSERT ALLOWED FILE TYPES INTO A NEW ARRAY
		if($images) array_push($Allowed, $mimeTypes['images']);
		if($audio) array_push($Allowed, $mimeTypes['audio']);
		if($video) array_push($Allowed, $mimeTypes['video']); 
		if($archives) array_push($Allowed, $mimeTypes['archives']);
		if($other) array_push($Allowed, $mimeTypes['other']);
		
		// REMAKE ARRAY INTO ONE-DIMENSIONAL
		for($i = 0; $i < count($Allowed); $i ++)
			foreach($Allowed[$i] as $key => $val)
				$all[$key] = $val;
		
		// REWRITE ARRAY
		$Allowed = $all;
	}

	// CHECK IF ALLOWED FILE TYPES COINCIDE WITH UPLOADING
	foreach($files as $key => $fileArr)
	{
		// IF FILE WAS NOT SELECTED FROM INPUT-FILE CONTINUE
		if(!$fileArr['name'])
		{
			unset($files[$key]);
			continue;
		}

		// VARIABLE TO CHECK UP RESULT OF COINCIDENCE
		$alw = false;	
		
		// THE TOTAL SIZE OF FILES
		$totSize = 0;

		// GET FILE FORMAT FROM UPLOADING FILE NAME
		$frmt = strtolower(substr_replace($fileArr['name'], "", 0, strrpos($fileArr['name'], ".") + 1));
			
		foreach($Allowed as $k => $v)
			if($v == $fileArr['type'] && $k == $frmt)
			{
				// IF THE FILE SIZE EXCEEDS THE ALLOWED
				if($size && ($fileArr['size'] / 1024) > $size)
					return false;
				
				$totSize += ($fileArr['size'] / 1024);
				
				$alw = true;
				break;
			}
		
		// CHECKING UP COINCIDENCE
		if(!$alw)
			return false;
	}

	// IF WE MAKE FOLDER
	if($dir)
	{
		if(!mkdir($path . $dir, 0755))
			exit("ERROR AT FOLDER CREATION");
		
		// MAKE FULL PATH	
		$path = $path . $dir . "/";
	}
	
	// ARRAY FOR UPLOUDED FILES
	$uplFiles = array();
	
	// UPLOAD FILES
	foreach($files as $key => $val)
	{
		// GET FILE FORMAT FROM UPLOADING FILE NAME
		$frmt = strtolower(substr_replace($val['name'], "", 0, strrpos($val['name'], ".")));
		
		// GENERATE A UNIQUE NAME FOR FILE
		if($uniq)
		{
			$uniqName = md5(uniqid(rand(), 1));
			$val['name'] = $uniqName . $frmt;
		}
		
		// INSERT UPPLOUDED ITEM TO ARRAY
		$uplFiles[$key] = $val['name'];

		// IF FILE IS NOT UPLOUDED
		if(!move_uploaded_file($val['tmp_name'], $path . $val['name']))
		{
			// DELETE DIR AND ITS ELEMENTS
			if($dir)
				DelDir($path);

			return false;
		}
	}
	
	return $uplFiles;
	
}

?>
