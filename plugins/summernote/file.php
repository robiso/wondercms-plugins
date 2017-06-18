<?php
// SummerNote plugin for WonderCMS, Server-side upload script

ini_set('display_errors', 0);
$contents_path = isset($_SESSION['contents_path']) ? $_SESSION['contents_path']:'files';
$contents_path = $contents_path ? $contents_path:'files';

$do = isset($_POST['do']) ? $_POST['do'] : (isset($_GET['do']) ? $_GET['do'] : '');

$image_exts = array('jpg', 'jpeg', 'png', 'gif', 'bmp','svg');
$document_ext = array('txt', 'text', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'zip', 'rar', '7z', 'pdf');

if ($do=='ul') {
	$type = isset($_POST['type']) ? $_POST['type'] : (isset($_GET['type']) ? $_GET['type'] : '');
	if ($_FILES['file']['name']) {
		if (!$_FILES['file']['error']) {
			$filename = $_FILES['file']['name'];
			$ext = strtolower(end((explode(".", $filename))));
			if ($type == 'images') {
				$exts = $image_exts;
			} else {
				$exts = $document_ext;
			}
			if (filesize($_FILES["file"]["tmp_name"])<=10485760) {
				if (in_array($ext, $exts)) {
					$destination = __DIR__."/../../$contents_path/$type";
					if (!file_exists($destination)) {
						mkdir($destination, 0775, true);
					}
					$destination = "$destination/$filename";
					$location = $_FILES["file"]["tmp_name"];
					if (move_uploaded_file($location, $destination)) {
						echo "$contents_path/$type/$filename";
					} else {
						echo  $message = 'Unable to move uploaded file';
					}
				} else {
					echo  $message = 'File extension is not allowed.';
				}
			} else {
				echo  $message = 'File size is over limit.';
			}
		} else {
			echo  $message = 'Your upload triggered the following error:  '.$_FILES['file']['error'];
		}
	} else {
		echo  $message = 'No file selected.';
	}
}
if ($do=='ls') {
	$type = isset($_POST['type']) ? $_POST['type'] : (isset($_GET['type']) ? $_GET['type'] : '');
	$dir = __DIR__."/../../$contents_path/$type";
	$list = array();
	if (!file_exists($dir)){
		mkdir($dir, 0755, true);
	}
	$dir = new DirectoryIterator($dir);
	foreach ($dir as $fileinfo) {
	    if ($fileinfo->isFile()) {
	        $ext = strtolower(pathinfo($fileinfo->getFilename(), PATHINFO_EXTENSION));
			if ($type == 'images') {
				if (in_array($ext, $image_exts)) {
					$list[] = $fileinfo->getFilename();
				}
			} else {
				$list[] = $fileinfo->getFilename();
			}
	    } else if ($fileinfo->isDir() && ! $fileinfo->isDot()) {
			$list[] = $fileinfo->getFilename();
		}
	}
	echo json_encode($list);
}
?>
