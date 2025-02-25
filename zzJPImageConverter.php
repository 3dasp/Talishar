<?php
$filename = "../jp_rap_image_locs.json";
$jsonObject = file_get_contents($filename);
$imageLocs = json_decode($jsonObject, true);

foreach ($imageLocs as $cardID => $imageURL) {
    CheckImageJP($cardID, $imageURL);
}

function CheckImageJP($cardID, $imageURL, $isDuplicate=false)
{
  $filename = "./WebpImages/" . $cardID . ".webp";
  $cardImagesUploadedFolder = "../CardImages/media/uploaded/public/cardimages/japanese/" . $cardID . ".webp"; // !! CardImages/ to be changed for your own folder name
  $cardImagesMissingFolder = "../CardImages/media/missing/cardimages/japanese/" . $cardID . ".webp"; // !! CardImages/ to be changed for your own folder name
  if (true)
  {
    echo("Image for " . $cardID . " does not exist.<BR>");
    echo("Downloading image from $imageURL <BR>");
    $handler = fopen($filename, "w");
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $imageURL);
    curl_setopt($ch, CURLOPT_FILE, $handler);
    curl_exec($ch);
    curl_close($ch);
    if(filesize($filename) < 10000) { unlink($filename); return; }
    if(file_exists($filename)) echo("Image for " . $cardID . " successfully retrieved.<BR>");
    if(file_exists($filename))
    {
      echo("Normalizing file size for " . $cardID . ".<BR>");
      $image = imagecreatefromwebp($filename);
      $image = imagescale($image, 450, 628);
      imagewebp($image, $cardImagesMissingFolder);
      // Free up memory
      imagedestroy($image);
    }
  }
  $concatFilename = "./concat/" . $cardID . ".webp";
  $cardSquaresUploadedFolder = "../CardImages/media/uploaded/public/cardsquares/japanese/" . $cardID . ".webp"; // !! CardImages/ to be changed for your own folder name
  $cardSquaresMissingFolder = "../CardImages/media/missing/cardsquares/japanese/" . $cardID . ".webp"; // !! CardImages/ to be changed for your own folder name
  if (true)
  {
    echo("Concat image for " . $cardID . " does not exist.<BR>");
    if(file_exists($filename))
    {
      echo("Attempting to convert image for " . $cardID . " to concat.<BR>");
      $image = imagecreatefromwebp($filename);
      $width = 540;
      $topHeight = 440;
      $botHeight = $width - $topHeight;
      
      $imageTop = imagecrop($image, ['x' => 0, 'y' => 0, 'width' => $width, 'height' => $topHeight]);
      $imageBottom = imagecrop($image, ['x' => 0, 'y' => 670, 'width' => $width, 'height' => $botHeight]);

      $dest = imagecreatetruecolor($width, $width);
      imagecopy($dest, $imageTop, 0, 0, 0, 0, $width, $topHeight);
      imagecopy($dest, $imageBottom, 0, $topHeight, 0, 0, $width, $botHeight);

      imagewebp($dest, $cardSquaresMissingFolder);
      // Free up memory
      imagedestroy($image);
      imagedestroy($dest);
      imagedestroy($imageTop);
      imagedestroy($imageBottom);
      if(file_exists($concatFilename)) echo("Image for " . $cardID . " successfully converted to concat.<BR>");
    }
  }
}


