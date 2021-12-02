<?php
$f=$_GET['f'];
if(!file_exists($f)){
 exit('File Tidak Ditemukan');
}

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"".$f."\"");
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".filesize($f));
ob_end_flush();
@readfile($f);

unlink($f);
?>