
//创建文件路径，多级路径
function CreatePath($path) {
    if (is_dir($path)) return true;
    $dirname =dirname($path);
    $isok = true;
    if (!file_exists($dirname)) {
        $isok = CreatePath($dirname);
    }
    return ($isok && is_writable($dirname)) ? mkdir($path) : false;
}

//列出某个目录下的所有文件
function listFolderFiles($dir,$basedir = ""){
    $array = [];
    foreach (new DirectoryIterator($dir) as $fileInfo) {
        if (!$fileInfo->isDot()) {
            if($fileInfo->isFile()){
                //echo $basedir.'/'.$fileInfo->getFilename()."<br />";
                array_push ($array, $basedir.'/'.$fileInfo->getFilename());
            }
            if ($fileInfo->isDir()) {
               //echo $basedir.'/'.$fileInfo->getFilename()."<br />";
               $files = listFolderFiles($fileInfo->getPathname(),$basedir.'/'.$fileInfo->getFilename());
               $array = array_merge ($array , $files);
            }
        }
    }
    return $array;
}
//合并指定文件集
function MergeToOne($basedir,$files,$saveFile){
    $fHandle = fopen($saveFile, "wb");
    foreach($files as $file){
        $filename = $basedir.$file;
        $filesize = filesize($filename);
        $rHandle = fopen($filename,"rb");
        $readInFile = fread($rHandle,$filesize);
        fclose($rHandle);
        fwrite($fHandle,$file."\n");
        fwrite($fHandle,$filesize."\n");
        fwrite($fHandle,$readInFile);
    }
    fclose($fHandle);
}

//从合并的文件中还原对应的文件及目录结构
function ExtractFromOne($dir,$file){
    $rHandle = fopen($file,"rb");
    while(!feof($rHandle)){
        $filename = fgets($rHandle);
        if(empty($filename)){
            continue;
        }
        $size = intval(fgets($rHandle));
        if($size == 0){
            continue;
        }
        $content = fread($rHandle,$size);

        $path_parts = pathinfo($dir.$filename);
        if(!CreatePath($path_parts['dirname']))
            continue;
        $fHandle = fopen($dir.trim($filename), "wb");
        fwrite($fHandle,$content);
        fclose($fHandle);
    }
    fclose($rHandle);
    
}

$basedir = './test';
$array = listFolderFiles($basedir);
echo join('\n',$array);
MergeToOne($basedir,$array,"t.zip");
ExtractFromOne('test',"t.zip");
