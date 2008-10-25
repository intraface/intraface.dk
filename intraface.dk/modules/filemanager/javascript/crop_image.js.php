
Event.observe( window, 'load', function() {
        
    document.getElementById('width').readOnly = true;
    document.getElementById('width').style.backgroundColor = '#EEEEEE';
	document.getElementById('height').readOnly = true;
	document.getElementById('height').style.backgroundColor = '#EEEEEE';
	document.getElementById('x').readOnly = true;
	document.getElementById('x').style.backgroundColor = '#EEEEEE';
	document.getElementById('y').readOnly = true;
	document.getElementById('y').style.backgroundColor = '#EEEEEE';
        
        
    new Cropper.Img(
        'image',
        { 
            
            
            minWidth: <?php if (isset($_GET['max_width'])): e($_GET['max_width']); else: echo 0; endif; ?>,
            minHeight: <?php if (isset($_GET['max_height'])): e($_GET['max_height']); else: echo 0; endif; ?>,
            <?php if (isset($_GET['unlock_ratio']) && intval($_GET['unlock_ratio']) == 0): ?>
            ratioDim: {
                x: <?php if (isset($_GET['max_width'])): e($_GET['max_width']); else: echo 0; endif; ?>,
                y: <?php if (isset($_GET['max_height'])): e($_GET['max_height']); else: echo 0; endif; ?>
            },
            <?php endif; ?>
            displayOnInit: true,
            onEndCrop: onEndCrop
         
         }
    );
} );


function onEndCrop(coords, dimensions) {
	document.getElementById('width').value = Math.floor(dimensions.width * <?php if (isset($_GET['size_ratio'])): echo doubleval($_GET['size_ratio']); else: echo 1; endif; ?>);
	document.getElementById('height').value = Math.floor(dimensions.height * <?php if (isset($_GET['size_ratio'])): echo doubleval($_GET['size_ratio']); else: echo 1; endif; ?>);
	document.getElementById('x').value = Math.round(coords.x1 * <?php if (isset($_GET['size_ratio'])): echo doubleval($_GET['size_ratio']); else: echo 1; endif; ?>);
	document.getElementById('y').value = Math.round(coords.y1 * <?php if (isset($_GET['size_ratio'])): echo doubleval($_GET['size_ratio']); else: echo 1; endif; ?>);
	
}