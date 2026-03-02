	<script type="text/javascript" src="{{ asset('public/js/jquery.min.js')}}"></script>
<style type="text/css">
	.btn {
		border-radius: 2px;
		overflow: hidden;
		position: relative;
		padding: 8px 14px 7px;
		font-size: 11px;
	}
	.btn-primary {
		background-color: #18c98e !important;
		border: 1px solid #18c98e !important;
		color: #fff !important;
		text-transform: capitalize;
		line-height: 1.42857143;
		text-align: center;
		white-space: nowrap;
		vertical-align: middle;
		font-weight: 600;
		display: inline-block;
		touch-action: manipulation;
		  
	}
	.btn-primary:hover{
		background-color: #167ccb !important;
		border: 1px solid #167ccb !important;
		color: #fff !important;
		cursor: pointer;
	}
	.btn-spacing 
	{
		margin-right: 5px;
		margin-bottom: 5px !important;
	}
</style>
    <form id="save_remote" method="POST" action ="" method="POST"  class="form-horizontal" enctype="multipart/form-data" data-toggle="validator" target="my_iframe" >{{csrf_field()}}
        <div class="col-sm-4 float-right" id="sketchpadapp" >
            <p><b>Signature</b></p>
           
            <canvas id="blank"  style='display:none'></canvas>
             <canvas class="roundCorners newSignature" id="newSignature"
      style="position: relative; margin: 0 0 10px 0; width:276px; height:100px;float: left; padding: 0; border: 1px solid #c4caac;"></canvas>

          <!-- <button type="button" class="btn btn-primary" onclick="signatureClear()">Clear signature</button> -->
          <input type = "hidden" id="saveSignature" name="saveSignature" value="" />
          <input type = "hidden" id="testSignature" name="testSignature" value="" />
          <input type = "hidden" id="test_sign" name="test_sign" value="test_sign" />
          <!-- <img id="saveSignature" alt="Saved image png"/ style="display: none"> -->
        </div>
        <div class="col-sm-4">
          <button type="button" class="btn btn-primary btn-spacing" onclick="signatureClear()">Clear </button>
          <button id="canSave" type="button" class="btn btn-primary btn-spacing"  onclick="signatureSave()">Submit</button>
        </div>
</form>
 
     


  <script>
    $(document).ready(function() {
      signatureCapture();
    });
  function signatureCapture() {
  var canvas = document.getElementById("newSignature");
  var context = canvas.getContext("2d");
  canvas.width = 276;
  canvas.height = 105;
  context.fillStyle = "#fff";
  context.strokeStyle = "#444";
  context.lineWidth = 1.5;
  context.lineCap = "round";
  context.fillRect(0, 0, canvas.width, canvas.height);
  var disableSave = true;
  var pixels = [];
  var cpixels = [];
  var xyLast = {};
  var xyAddLast = {};
  var calculate = false;
  {   //functions
    function remove_event_listeners() {
      canvas.removeEventListener('mousemove', on_mousemove, false);
      canvas.removeEventListener('mouseup', on_mouseup, false);
      canvas.removeEventListener('touchmove', on_mousemove, false);
      canvas.removeEventListener('touchend', on_mouseup, false);

      document.body.removeEventListener('mouseup', on_mouseup, false);
      document.body.removeEventListener('touchend', on_mouseup, false);
    }

    function get_coords(e) {
      var x, y;

      if (e.changedTouches && e.changedTouches[0]) {
        var offsety = canvas.offsetTop || 0;
        var offsetx = canvas.offsetLeft || 0;

        x = e.changedTouches[0].pageX - offsetx;
        y = e.changedTouches[0].pageY - offsety;
      } else if (e.layerX || 0 == e.layerX) {
        x = e.layerX;
        y = e.layerY;
      } else if (e.offsetX || 0 == e.offsetX) {
        x = e.offsetX;
        y = e.offsetY;
      }

      return {
        x : x, y : y
      };
    };

    function on_mousedown(e) {
      e.preventDefault();
      e.stopPropagation();

      canvas.addEventListener('mouseup', on_mouseup, false);
      canvas.addEventListener('mousemove', on_mousemove, false);
      canvas.addEventListener('touchend', on_mouseup, false);
      canvas.addEventListener('touchmove', on_mousemove, false);
      document.body.addEventListener('mouseup', on_mouseup, false);
      document.body.addEventListener('touchend', on_mouseup, false);

      empty = false;
      var xy = get_coords(e);
      context.beginPath();
      pixels.push('moveStart');
      context.moveTo(xy.x, xy.y);
      pixels.push(xy.x, xy.y);
      xyLast = xy;
      
    };

    function on_mousemove(e, finish) {
      e.preventDefault();
      e.stopPropagation();

      var xy = get_coords(e);
      var xyAdd = {
        x : (xyLast.x + xy.x) / 2,
        y : (xyLast.y + xy.y) / 2
      };

      if (calculate) {
        var xLast = (xyAddLast.x + xyLast.x + xyAdd.x) / 3;
        var yLast = (xyAddLast.y + xyLast.y + xyAdd.y) / 3;
        pixels.push(xLast, yLast);
      } else {
        calculate = true;
      }

      context.quadraticCurveTo(xyLast.x, xyLast.y, xyAdd.x, xyAdd.y);
      pixels.push(xyAdd.x, xyAdd.y);
      context.stroke();
      context.beginPath();
      context.moveTo(xyAdd.x, xyAdd.y);
      xyAddLast = xyAdd;
      xyLast = xy;
      $("#testSignature").val('tested');

    };

    function on_mouseup(e) {
      remove_event_listeners();
      disableSave = false;
      context.stroke();
      pixels.push('e');
      calculate = false;
    };
  }
  canvas.addEventListener('touchstart', on_mousedown, false);
  canvas.addEventListener('mousedown', on_mousedown, false);
}


function signatureSave(e) {



  canvass = document.getElementById('blank');
  ctx = canvass.getContext('2d');
    if(canvass.toDataURL() == document.getElementById('newSignature').toDataURL()){
      alert('Cannot submit without tenant Signature ');
      return false; 
    }else{
      var canvas = document.getElementById("newSignature");// save canvas image as data url (png format by default)
      var dataURL = canvas.toDataURL("image/png");
      document.getElementById("saveSignature").value = dataURL; 
      var testSignature = $("#testSignature").val();
      
        if(testSignature != ""){
          $.ajax({
            method: 'POST', // Type of response and matches what we said in the route
            url: "{{route('terminationSignatureStore')}}",
            data: {'saveSignature' : dataURL,'testSignature' : testSignature,"_token": "{{ csrf_token() }}"}, // a JSON object to send back
              success: function(response){ // What to do if we succeed
	
                window.top.location.href = response; 
              },
          });
        }else{
          alert("Can't Submit Without Tenant Signature");
          return false;
        }

    }
  

  
};

function signatureClear() {
  var canvas = document.getElementById("newSignature");
  var context = canvas.getContext("2d");
  context.clearRect(0, 0, canvas.width, canvas.height);
  context.fillStyle = "#fff";
  context.strokeStyle = "#444";
  canvas.width = 276;
  canvas.height = 105;
  context.fillStyle = "#fff";
  context.strokeStyle = "#444";
  context.lineWidth = 1.5;
  context.lineCap = "round";
  context.fillRect(0, 0, canvas.width, canvas.height);
  
  $("#testSignature").val("");
}
</script>
