 
 

<!DOCTYPE html>
<html>
<head>
 
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.25/webcam.min.js"></script>
 
</head>
<body>
  
<div class="container">
    <h1 class="text-center">Capture webcam image with php and jquery - ItSolutionStuff.com</h1>
   
    <form method="POST" action="<?php echo site_url('Order/saveImage'); ?>">
        <div class="row">
            <div class="col-md-6">
                <div id="my_camera"></div>
                <br/>
                <input type=button value="Take Snapshot" onClick="take_snapshot()">
                <input type="hidden" name="image" class="image-tag">
            </div>
            <div class="col-md-6">
                <div id="results">Your captured image will appear here...</div>
            </div>
			<textarea  id="imgtxt" name="iamge">
				
			</textarea>
			<img src="" id="teimg">
            <div class="col-md-12 text-center">
                <br/>
                <button class="btn btn-success dz-btn--filled">Submit</button>
            </div>
        </div>
    </form>
</div>
  
<!-- Configure a few settings and attach camera -->
<script language="JavaScript">
    Webcam.set({
        width: 490,
        height: 390,
        image_format: 'jpeg',
        jpeg_quality: 90
    });
  
    Webcam.attach( '#my_camera' );
  
    function take_snapshot() {
        Webcam.snap( function(data_uri) {
            $(".image-tag").val(data_uri);
            $("#imgtxt").val(data_uri);
            document.getElementById('results').innerHTML = '<img src="'+data_uri+'"/>';
			 document.getElementById('teimg').src = data_uri;
            
			
			
        } );
    }
</script>
 
</body>
</html>