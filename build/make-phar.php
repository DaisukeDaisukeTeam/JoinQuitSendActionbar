<?php

$file_phar = "JoinQuitSendActionbar.phar";
if(file_exists($file_phar)){
	echo "Phar file already exists, overwriting...";
	echo PHP_EOL;
	Phar::unlinkArchive($file_phar);
}

$files = [];
$dir = getcwd().DIRECTORY_SEPARATOR;

function addPhar(string $targetfolder, string $basepath, array &$files){
	if(is_dir($targetfolder)){
		foreach(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($basepath.$targetfolder)) as $path => $file){
			if($file->isFile() === false){
				continue;
			}
			$files[str_replace($basepath, "", $path)] = $path;
		}
	}
}

addPhar("src", $dir, $files);
addPhar("resources", $dir, $files);

$files["plugin.yml"] = $dir."plugin.yml";

echo "Compressing ".count($files)." files...".PHP_EOL;
$phar = new Phar($file_phar, 0);
$phar->startBuffering();
$phar->setSignatureAlgorithm(\Phar::SHA1);
$phar->buildFromIterator(new \ArrayIterator($files));
$phar->setStub("<?php __HALT_COMPILER(); ?>");

if(isset($argv[1])&&$argv[1] === "withoutCompressAll"){
	foreach($phar as $file => $finfo){
		/** @var \PharFileInfo $finfo */
		if($finfo->getSize() > (1024 * 512)){
			$finfo->compress(\Phar::GZ);
		}
	}
}else{
	$phar->compressFiles(Phar::GZ);
}

$phar->stopBuffering();
echo "end.".PHP_EOL;
