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
                $tmp_img = imagecreatetruecolor($width, $height);
                // Copy/crop action
                imagecopyresampled($tmp_img, $thumb_img, 0, 0, $crop_x, $crop_y, $new_width, $new_height, $w, $h);
                // If false, send browser header for output to browser window
                if($SetFileName == false)
                    header('Content-Type: '.$this->original['type']);
                // Output proper image type
                if($this->original['type'] == 'image/gif')
                    //imagegif($tmp_img);
                    ($SetFileName !== false)? $retval = imagegif($tmp_img, $SetFileName, $quality) : imagegif($tmp_img);
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
$uploadStatus = $_FILES['filename']['error'];

if($uploadStatus == UPLOAD_ERR_OK) {
  if(is_file($pool . "/" . $uploadFile)) {
    messageUser("Le fichier $uploadFile existe déjà", "alert-danger");
  } else {
    //array_push($messages, array(preg_replace("/,/", ",\n", json_encode($_FILES)), "alert-info", 0));
    if(move_uploaded_file($_FILES['filename']['tmp_name'], $pool . "/" . $uploadFile)) {
      try {
        $type = mime_content_type($pool . "/" . $uploadFile);
        //$type = $_FILES['filename']['type'];
        $filenameInserted = $db->exec(
            "INSERT OR REPLACE INTO documents (filename, filetype, attached_notes) VALUES ('$uploadFile', '$type', ',$title,');"
        );
        messageUser("Fichier <b>$uploadFile</b> ajouté", "alert-success");
      } catch (Throwable $error) {
        $filenameInserted = false;
        messageUser($error->getMessage() . " in " .$error->getFile() . $error->getLine(), "alert-danger");
      }
      if($filenameInserted) { 
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
            messageUser("Erreur pour les miniatures : " . $error->getMessage() . " in " . $error->getFile() . $error->getLine(), "alert-warning");
          }
        }
      } else {
        messageUser("Le fichier <b>$uploadFile</b> n'a pas pu être ajouté à la base de données", "alert-danger");
        try {
          $db->exec("DELETE FROM documents WHERE filename = '$uploadFile'");
        } catch (Throwable $error) {
          messageUser("On l'a copié, maintenant on ne peut plus l'enlever !", "alert-warning");
        } finally {
          return -1;
        }
      }
    } else {
      messageUser("Le fichier <b>$uploadFile</b> n'a pas pu être copié. Vérifiez les permissions sur le dossier <b>$pool</b> dans le serveur", "alert-danger");
      return -1;
    }
  }
} else {
  switch($uploadStatus) {
    case UPLOAD_ERR_INI_SIZE:
    case UPLOAD_ERR_FORM_SIZE:
      messageUser("Fichier trop gros - limite : " . round(file_upload_max_size()/1048576) . " Mo", "alert-danger");
      break;
    case UPLOAD_ERR_PARTIAL:
      messageUser("Le fichier n'a pas pu être récupéré", "alert-danger");
      break;
    case UPLOAD_ERR_NO_FILE:
      messageUser("Le fichier est vide", "alert-danger");
      break;
    default:
      messageUser("Erreur interne : " . $_FILES['newfile']['error'], "alert-danger");
      break;
  }
  // TODO Tell caller we failed :
  return -1;
}

// Only return an answer if the engine called for this file directly :
if($programRoot . "/engine/" . $engine_call == __file__) {
  get_document_list();
  return_answer();
}

