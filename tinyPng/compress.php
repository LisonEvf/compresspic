<?php
$count = 0;
$apikey = array(
  "ylJjcfua-q5A_ZdYkZO8VBnBCY3yNOed",
  "6HzqntwfyHBqTFBtuH_i3hRnTkG_STgI",
  "BfgnUr-60VR115yqtoDvgUsY0zNY0sCF",
  "MfbGKVflTgAeRurxQvfOfu1ve0yzcOfA",
  "wZW1HyGoe2PJqPVZcYZdppc6Vf-BzlUR",
  "FcRv7PcqogEyeyi4H3dF9_jkzd_OPkkY"
  );
$keyid = 0;

function deldir($dir) {
  //先删除目录下的文件：
  $dh=opendir($dir);
  while ($file=readdir($dh)) {
    if($file!="." && $file!="..") {
      $fullpath=$dir."/".$file;
      if(!is_dir($fullpath)) {
          unlink($fullpath);
      } else {
          deldir($fullpath);
      }
    }
  }
 
  closedir($dh);
  //删除当前文件夹：
  if(rmdir($dir)) {
    return true;
  } else {
    return false;
  }
}

function compresspic($filePath, $errorPath){
    $keys = $GLOBALS['apikey'];
    $kid = $GLOBALS['keyid'];
    $key = $keys[$kid];
    $input = $filePath;
    $output = "pic_out/$filePath";

    $request = curl_init();
    curl_setopt_array($request, array(
      CURLOPT_URL => "https://api.tinify.com/shrink",
      CURLOPT_USERPWD => "api:" . $key,
      CURLOPT_POSTFIELDS => file_get_contents($input),
      CURLOPT_BINARYTRANSFER => true,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_HEADER => true,
      /* Uncomment below if you have trouble validating our SSL certificate.
         Download cacert.pem from: http://curl.haxx.se/ca/cacert.pem */
      // CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
      CURLOPT_SSL_VERIFYPEER => true
    ));

    $response = curl_exec($request);
    // if (1 == 2) {
    if (curl_getinfo($request, CURLINFO_HTTP_CODE) === 201) {
      /* Compression was successful, retrieve output from Location header. */
      $headers = substr($response, 0, curl_getinfo($request, CURLINFO_HEADER_SIZE));
      foreach (explode("\r\n", $headers) as $header) {
        if (strtolower(substr($header, 0, 10)) === "location: ") {
          $request = curl_init();
          curl_setopt_array($request, array(
            CURLOPT_URL => substr($header, 10),
            CURLOPT_RETURNTRANSFER => true,
            /* Uncomment below if you have trouble validating our SSL certificate. */
            // CURLOPT_CAINFO => __DIR__ . "/cacert.pem",
            CURLOPT_SSL_VERIFYPEER => true
          ));
          file_put_contents($output, curl_exec($request));
          $GLOBALS['count'] += 1;
          echo "process $filePath\t key:$key\tuse:{$GLOBALS['count']}times\n";
        }
      }
    } else if(curl_getinfo($request, CURLINFO_HTTP_CODE) === 429){
      echo "key:$key is run out of";
      if($kid<count($keys)-1){
        $GLOBALS['count'] = 0;
        $GLOBALS['keyid'] += 1;
        compresspic($filePath, $errorPath);
      }else{
        $GLOBALS['count'] = 0;
        $GLOBALS['keyid'] += 1;
        fwrite(STDOUT, "Please Enter a new APIKey: ");  
        $GLOBALS['apikey'][$GLOBALS['keyid']] = trim(fgets(STDIN)); 
        compresspic($filePath, $errorPath);
      }


    }else {
      print(curl_error($request));
      /* Something went wrong! */
      print("\n Compression failed \n");
      print("$filePath  pic_out/$errorPath\n");
      copy($filePath, "pic_out/$errorPath");
    }
      

}


function tree($directory, $errDir)
{
    $mydir = dir($directory);
    while($file = $mydir->read())
    {
        if((is_dir("$directory/$file")) AND ($file!=".") AND ($file!=".."))
        {
            // print("pic_out/$directory/$file \n");
            mkdir("pic_out/$directory/$file");
            mkdir("pic_out/$errDir/$file");
            tree("$directory/$file", "$errDir/$file");
        }
        else
        {
          $filePath = "$directory/$file";
          $errorPath = "$errDir/$file";

          // print("-----$filePath \n");
          if(substr($filePath, -4) == ".png" OR substr($filePath, -4) == ".jpg")
          {
            compresspic($filePath, $errorPath);
          }else if(($file!=".") AND ($file!="..")){
            copy($filePath, "pic_out/$directory/$file");
          }
        }
    }
    $mydir->close();
}

if(!file_exists("pic_out/pic"))
  mkdir("pic_out/pic");
if(!file_exists("pic_out/error"))
  mkdir("pic_out/error");

tree("pic", "error");

?>