<?php
if(isset($_GET['subdir'])) {
  $subdir = 'bkmg-sources/' . $_GET['subdir'];
  $images = array();
  if($handle = opendir($subdir)) {
    while(false !== ($entry = readdir($handle))) {
      if($entry != "." && $entry != ".." && is_file($subdir . '/' . $entry)) {
        $images[] = $entry;
        //$images[] = $subdir . '/' . $entry;
      }
    }
    closedir($handle);
  }
  echo json_encode($images);
}
?>


