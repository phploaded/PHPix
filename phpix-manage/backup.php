<?php 
 
$aid = htmlentities($_GET['aid']); 

if($aid==''){
$al['title'] = 'Full System Backup';
$al['slug'] = 'full';
$al['updated'] = time();
$al['thumb'] = 'fdf'; // anything but not blank
} else {
$al = mysqli_fetch_assoc(mysqli_query($con, "SELECT * FROM `".$prefix."albums` WHERE `id`='$aid'"));
}

function deleteAllZipFiles($directory, $slug) {
    // Define the pattern for matching ZIP files
    $pattern = $directory . '/'.$slug.'_archive_*.zip';


    // Use glob to find files matching the pattern
    $zipFiles = glob($pattern);

    // Loop through the array of files and delete each one
    foreach ($zipFiles as $zipFile) {
        if (is_file($zipFile)) {
            unlink($zipFile);
            echo "Deleted: " . $zipFile . "<br>";
        }
    }

    echo "All ZIP files have been deleted.";
}

$allowed = array("fhd", "hd", "qhd", "thumb");
if(isset($_GET['dir'])){
if(in_array($_GET['dir'], $allowed)){
$dir = $_GET['dir'];
} else {
$dir = 'full';
}
} else { $dir = 'full'; }

// Initialize session variables if not already set
if (!isset($_SESSION['currentSize'])) {
    $_SESSION['currentSize'] = 0;
    $_SESSION['processedLines'] = [];
    $_SESSION['zipCount'] = 1;
}

$pathfile = "temp/".$al['slug']."_paths.txt";
$zipCountFormatted = sprintf('%02d', $_SESSION['zipCount']);
$zipfile = "temp/".$al['slug']."_archive_" . $zipCountFormatted . ".zip";


if(isset($_GET['delete'])){ 
deleteAllZipFiles('temp', $al['slug']);
echo'<script>
document.location.href = "phpix-manage.php?page=backup&aid='.$_GET['aid'].'";
</script>';
die();
}


if(isset($_GET['make'])){

$maxSize = 500 * 1024 * 1024; // 500MB in bytes




$zip = new ZipArchive;

if ($zip->open($zipfile, ZipArchive::CREATE) === TRUE) {
    // Open the input file in read mode
    $input = fopen($pathfile, "r");

    if ($input) {
        $lines = []; // Array to store lines of the input file

        // Read each line of the input file until the end
        while (!feof($input)) {
            $line = trim(fgets($input));
            if ($line != '') {
                $lines[] = $line; // Store line in the array

                // Skip lines that have already been processed
                if (in_array($line, $_SESSION['processedLines'])) {
                    continue;
                }

                $ldata = explode(' ||| ', $line);
                $filePath = $dir . '/' . $ldata[0];

                if (file_exists($filePath)) {
                    $fileSize = filesize($filePath);

                    // Check if adding this file will exceed the maximum size
                    if ($_SESSION['currentSize'] + $fileSize > $maxSize) {
                        break; // Stop adding files if the maximum size is reached
                    }

                    $zip->addFile($filePath, $ldata[1]);
                    $_SESSION['currentSize'] += $fileSize;
                    $_SESSION['processedLines'][] = $line; // Store the processed line
                } else {
                    echo $ldata[0] . ' was not found.<br>';
                }
            }
        }
        // Close the input file
        fclose($input);

        // All files are added, so close the zip file.
        $zip->close();

        // Check if there are more lines to process
        if (count($lines) > count($_SESSION['processedLines'])) {
            $_SESSION['zipCount']++; // Increment ZIP count
            $_SESSION['currentSize'] = 0; // Reset current size

echo'<br>Making part-'.$_SESSION['zipCount'].' of backup...<br>';

// Reload the page to create the next ZIP file
echo'<script>
document.location.reload();
</script>';
die();
        } else {
            // Clear session variables when all files are processed
unset($_SESSION['zipCount']);
unset($_SESSION['currentSize']);
unset($_SESSION['processedLines']);
            echo 'All files have been processed and zipped.';
        }
    } else {
        echo 'Failed to open the input file.<br>';
    }
} else {
    echo 'Failed to create or open the ZIP file.<br>';
}
	echo'<br>New backup created successfully.<br>';

echo'<script>
document.location.href = "phpix-manage.php?page=backup&aid='.$_GET['aid'].'";
</script>';
die();
}





sadmin_title('Backup <small>'.$al['title'].'</small>'); 

function sanitizeString($input) {
    // Use a regular expression to allow only letters, numbers, hyphens, and underscores
    return preg_replace('/[^a-zA-Z0-9_ \-]/', '', html_entity_decode($input));
}

// Function to get all child folders recursively and their files
function getFolderPaths($conn, $folder_id, $path = "") { 
global $prefix, $bytes, $folders; 
    $paths = [];

	$folders = $folders + 1;

    // Fetch files for this folder
    $files = getFilesInFolder($conn, $folder_id);
    $currentPath = $path;
    if (!empty($files)) {
        foreach ($files as $file) {
            $paths[] = $file. " ||| " . $currentPath . "/" . $file;
        }
    }

    // Fetch child folders
    $sql = "SELECT id, title FROM ".$prefix."albums WHERE parent = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $folder_id);  // 's' because id is varchar
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $childPath = $currentPath . "/" . sanitizeString($row['title']);
        $childPaths = getFolderPaths($conn, $row['id'], $childPath);

        // Only add the current folder if it or any of its children have files
        if (!empty($childPaths)) {
            $paths = array_merge($paths, $childPaths);
        }
    }

    $stmt->close();
    return $paths;
}

// Function to get files in a folder
function getFilesInFolder($conn, $folder_id) { 
global $prefix, $bytes; 
    $files = [];
    $sql = "SELECT url, size FROM ".$prefix."uploads WHERE folder = ? AND folder!=''";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $folder_id);  // 's' because folder id is varchar
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $files[] = $row['url'];
		$bytes = $bytes + $row['size'];
    }

    $stmt->close();
    return $files;
}






// Start folder ID
$start_folder_id = $aid;

$bytes = 0;
$folders = 0;

// Get all paths
$allPaths = getFolderPaths($con, $start_folder_id);

// Write paths to a text file
$file = fopen($pathfile, "w");
foreach ($allPaths as $path) {
    fwrite($file, $path . PHP_EOL);
}
fclose($file);
// echo "Nested folder paths have been written to nested_paths.txt";


$tfile = 'cover/'.$al['thumb'];

if($al['thumb']!='' && file_exists($tfile)){
$thumb = $domain.''.$tfile;
} else {
$thumb = $domain.'phpix-libs/images/holder.svg';
}


if(file_exists($zipfile)){
$btn = '<a class="confirm btn btn-danger pull-right" download target="_blank" href="'.$domain.'phpix-manage.php?page=backup&aid='.$aid.'&delete=yes">Delete</a>';
 

   // Define the pattern for matching ZIP files
    $pattern = 'temp/'.$al['slug'].'_archive_*.zip';


    // Use glob to find files matching the pattern
    $zipFiles = glob($pattern);
	$pfile = '';
	$tbytes = 0;

    // Sort the array of filenames
    sort($zipFiles);

    // Loop through the array of files and print each one's name and size
    foreach ($zipFiles as $zipFile) {
        if (is_file($zipFile)) {
            $fileSize = filesize($zipFile);
			$tbytes = $tbytes + $fileSize;
            $pfile = $pfile.'<tr><td>' . basename($zipFile) . ' </td>
<td><span class="badge">' . xsize($fileSize) . '</span></td>
<td><a class="btn btn-success btn-xs" download target="_blank" href="'.$domain.''.$zipFile.'">Download</a></td></tr>';
        }
    }

$bkstats = '<br /><h4>Backup found !</h4><ul><li>Total backup size : '.xsize($tbytes).'</li>
<li>Created on : '.xdate(filemtime($zipfile)).'</li>
</ul><table class="table table-striped table-hover ">
  <thead>
    <tr>
      <th>File name</th>
      <th>File size</th>
      <th>Download link</th>
    </tr>
  </thead>
  <tbody>
    '.$pfile.'
  </tbody>
</table>';
} else {
$btn = 'Choose image quality for backup<div class="btn-group pull-right">
<a class="btn btn-primary" href="'.$domain.'phpix-manage.php?page=operations&method=generate&dir=fhd&albumid='.$aid.'">Full HD</a>
<a class="btn btn-info" href="'.$domain.'phpix-manage.php?page=operations&method=generate&dir=hd&albumid='.$aid.'">HD</a>
<a class="btn btn-warning" href="'.$domain.'phpix-manage.php?page=operations&method=generate&dir=qhd&albumid='.$aid.'">QHD</a>
<a class="btn btn-default" href="'.$domain.'phpix-manage.php?page=operations&method=generate&dir=thumb&albumid='.$aid.'">Thumb</a>
<a class="btn btn-success" href="'.$domain.'phpix-manage.php?page=backup&aid='.$aid.'&make=yes">Original</a>
</div>';
$bkstats = '';
}

echo'<div class="clearfix"></div><div class="panel panel-info">
  <div class="panel-heading">Backup Information</div>
  <div class="panel-body">
	  <div class="col-xs-12 col-sm-6 col-md-3"><img class="img-thumbnail" src="'.$thumb.'" /></div>
	  <div class="col-xs-12 col-sm-6 col-md-9">
		  <h4 >Latest stats for new backup </h4><ul>
			<li>Total files : '.count($allPaths).'</li>
			<li>Original data size : '.xsize($bytes).'</li>
			<li>Total sub albums : '.$folders.'</li>
		  </ul>'.$bkstats.'
	  </div>
  </div>

<div class="panel-footer">
'.$btn.'
<div class="clearfix"></div>
</div>

</div>';




?>