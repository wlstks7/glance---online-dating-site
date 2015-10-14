<?php  

$_SESSION["form_token_profile_uploader"] = md5(uniqid());


?>
<!DOCTYPE html>
<html>
  <head>
    <title>cropit</title>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="stylesheet" href="../assets/semantic/semantic.min.css">
    <link rel="stylesheet" href="../assets/croppic/croppic.css">
    <script src="../assets/modernizr-2.6.2.min.js"></script>
    <style type="text/css">


    #cropContainer{
      max-width: 300px;
      margin: auto auto;
    }

    #croppic {
        margin: 17px auto;
    }

    </style>
  </head>
  <body>

    <div id="cropContainer" class="">
      <div id="croppic"></div>
      <div class="ui button blue fluid" id="btn_findImage"><i class="icon photo"></i>Find Your Image</div>
      <div style="margin-top:8px;z-index:1000000000;" class="ui button gray fluid" id="btn_cancel"><i class="icon remove circle"></i>Cancel</div>
    </div>

    <div id="form_token" data-id="<?php echo $_SESSION["form_token_profile_uploader"]; ?>"></div>
    <script src='../assets/jquery.min.js?=11115'></script>
    <script src="../assets/semantic/semantic.min.js?=11115"></script>
    
    <script src="../assets/croppic/load-image.js"></script>
    <script src="../assets/croppic/load-image-ios.js"></script>
    <script src="../assets/croppic/load-image-orientation.js"></script>
    <script src="../assets/croppic/load-image-meta.js"></script>
    <script src="../assets/croppic/load-image-exif.js"></script>
    <script src="../assets/croppic/load-image-exif-map.js"></script>
    <script src="../assets/croppic/croppic.js"></script>
  
    <script>
    
      var DEF_CLICK = "click";

      $(function(){

        $("#btn_ok").hide();

        $("#btn_cancel").on(DEF_CLICK, function(){

          parent.closeCrop();
        });
        
        var i = $("#form_token").attr("data-id");

        var croppicHeaderOptions = {
            cropData:{
              "i":i
            },
            cropUrl:'../func/profile_uploader.php',
            customUploadButtonId:'btn_findImage',
            modal:false,
            imgEyecandy:false,
            processInline:true,
            rotateFactor:90,
            zoomFactor:20,
            doubleZoomControls:false,
            loaderHtml:'<div class="loader bubblingG"><span id="bubblingG_1"></span><span id="bubblingG_2"></span><span id="bubblingG_3"></span></div> ',
            onBeforeImgUpload: function(){ console.log('onBeforeImgUpload') },
            onAfterImgUpload: function(){ console.log('onAfterImgUpload') },
            onImgDrag: function(){ console.log('onImgDrag') },
            onImgZoom: function(){ console.log('onImgZoom') },
            onAfterRemoveCroppedImg: function(){ 
              $("#btn_ok").hide();
              $("#btn_findImage").show();
              console.log('onAfterRemoveCroppedImg') },
            onBeforeImgCrop: function(){ console.log('onBeforeImgCrop') },
            onAfterImgCrop:function(){ 
              
              //console.log('onAfterImgCrop');
              $("#btn_findImage").hide();

              var image = $(".croppedImg").attr("src");
              parent.saveProfileImage_croppic(image);
              
            },
            onError:function(errormessage){ console.log('onError:'+errormessage) }
        } 
        var croppic = new Croppic('croppic', croppicHeaderOptions);

      });

    </script>
  </body>
</html>
