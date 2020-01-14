<?php

// retrieved from https://stackoverflow.com/questions/28002244/crop-resize-image-function-using-gd-library

class ImageFactory {

        protected   $original;
        public      $destination;

        public  function FetchOriginal($file) {
                $size                       =   getimagesize($file);
                $this->original['width']    =   $size[0];
                $this->original['height']   =   $size[1];
                $this->original['type']     =   $size['mime'];
                return $this;
        }

        public  function Thumbnailer($thumb_target='', $width=60, $height=60, $SetFileName=false, $quality=80) {
                // Set original file settings
                $this->FetchOriginal($thumb_target);
                // Determine kind to extract from
                if($this->original['type'] == 'image/gif')
                    $thumb_img  =   imagecreatefromgif($thumb_target);
                elseif($this->original['type'] == 'image/png') {
                        $thumb_img  =   imagecreatefrompng($thumb_target);
                        $quality    =   7;
                }
                elseif($this->original['type'] == 'image/jpeg')
                        $thumb_img  =   imagecreatefromjpeg($thumb_target);
                else
                    return false;
                // Assign variables for calculations
                $w  =   $this->original['width'];
                $h  =   $this->original['height'];
                // Calculate proportional height/width
                if($w > $h) {
                        $new_height =   $height;
                        $new_width  =   floor($w * ($new_height / $h));
                        $crop_x     =   ceil(($w - $h) / 2);
                        $crop_y     =   0;
                }
                else {
                        $new_width  =   $width;
                        $new_height =   floor( $h * ( $new_width / $w ));
                        $crop_x     =   0;
                        $crop_y     =   ceil(($h - $w) / 2);
                }
                // New image
                $tmp_img = imagecreatetruecolor($width,$height);
                // Copy/crop action
                imagecopyresampled($tmp_img, $thumb_img, 0, 0, $crop_x, $crop_y, $new_width, $new_height, $w, $h);
                // If false, send browser header for output to browser window
                if($SetFileName == false)
                    header('Content-Type: '.$this->original['type']);
                // Output proper image type
                if($this->original['type'] == 'image/gif')
                    imagegif($tmp_img);
                elseif($this->original['type'] == 'image/png')
                    ($SetFileName !== false)? imagepng($tmp_img, $SetFileName, $quality) : imagepng($tmp_img);
                elseif($this->original['type'] == 'image/jpeg')
                    ($SetFileName !== false)? imagejpeg($tmp_img, $SetFileName, $quality) : imagejpeg($tmp_img);
                // Destroy set images
                if(isset($thumb_img))
                    imagedestroy($thumb_img); 
                // Destroy image
                if(isset($tmp_img))
                    imagedestroy($tmp_img);
        }
}


require_once 'engine.php';

load_db();

$uploadFile = basename($_FILES['filename']['name']);

if(is_file($pool . "/" . $uploadFile)) {
  array_push($messages, array("Le fichier $uploadFile existe déjà", "alert-danger"));
  $title = "";
} else {
  try {
    $type = mime_content_type($_FILES['filename']['tmp_name']);
    $filenameInserted = $db->exec(
        "INSERT OR REPLACE INTO documents (filename, filetype, attached_notes) VALUES ('$uploadFile', '$type', ',$title,');"
    );
  } catch (Throwable $error) {
    $filenameInserted = false;
    array_push($messages, array($error->getMessage(), "alert-warning"));
  }
  if($filenameInserted) { 
    if(move_uploaded_file($_FILES['filename']['tmp_name'], $pool . "/" . $uploadFile)) {
      array_push($messages, array("Fichier <b>$uploadFile</b> ajouté", "alert-success"));
      if(explode("/", $type)[0] == "image") {
        try {
          $thumbnails = $pool . "/thumbnails";
          if(! is_dir($thumbnails)) { mkdir($thumbnails); }
          $ImageMaker = new ImageFactory();
          $ImageMaker->Thumbnailer($pool . "/" . $uploadFile, 120, 120, $thumbnails . "/" . $uploadFile);
          /*
          $thumbnail = new Imagick($pool . "/" . $uploadFile);
          $image->cropThumbnailImage(100, 100);
          $image->writeImage($thumbnails . "/" . $uploadFile);
           */
        } catch (Throwable $error) {
          array_push($messages, array("Erreur pour les miniatures : " . $error->getMessage(), "alert-warning"));
          ;
        }
      }
    } else {
      array_push($messages, array("Le fichier <b>$uploadFile</b> n'a pas pu être copié. Vérifiez les permissions sur le dossier <b>$pool</b> dans le serveur", "alert-danger"));
      if($filenameInserted) {
        try {
          $db->exec("DELETE FROM documents WHERE filename = '$uploadFile'");
        } catch (Throwable $error) {
          array_push($messages, array($error->getMessage(), "alert-warning"));
        }
      }
      // TODO We should tell a calling process like createFromDoc that this failed.
    }
  } else {
    array_push($messages, array("Le fichier <b>$uploadFile</b> n'a pas pu être ajouté à la base de données", "alert-danger"));
  }
}

// Only return an answer if this was not included from elsewhere :
if(get_included_files()[0] == __FILE__) {
  get_document_list();
  return_answer();
}

