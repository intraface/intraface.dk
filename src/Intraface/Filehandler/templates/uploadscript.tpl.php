<?php
$filemanager = new FileManager($kernel);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="da">
    <head>
        <script type="text/javascript">
function upload() {
    // hide old iframe
    var par = window.parent.document;
    var num = par.getElementsByTagName('iframe').length - 1;
    var iframe = par.getElementsByTagName('iframe')[num];

    iframe.className = 'hidden';

    // create new iframe
    var new_iframe = par.createElement('iframe');
    new_iframe.src = '<?php e(url(null)); ?>';
    new_iframe.frameBorder = '0';
    par.getElementById('iframe').appendChild(new_iframe);

    // add image progress
    var images = par.getElementById('images');
    var new_div = par.createElement('div');
    var new_img = par.createElement('img');
    new_img.src = '<?php e(url('/images/indicator.gif')); ?>';
    new_img.className = 'load';
    new_div.appendChild(new_img);
    images.appendChild(new_div);

    // send
    var imgnum = images.getElementsByTagName('div').length - 1;
    document.iform.imgnum.value = imgnum;

    setTimeout('document.iform.submit()', 10000);
}
</script>

<style>
.formrow label {
    width: 8em;
    float: left;
    font-family: Arial, Verdana, sans-serif;
    font-size: small;
}
.formrow  {
    clear: both;
}

form {
    width: 90%;
}

legend {
    font-weight: bold;
    margin-bottom: 0.5em;
}

fieldset{
    padding: 0.5em;
}
body {
    width: 90%;
    margin: 0;
    padding: 0;
}
</style>
</head>
<body>
        <form name="iform" action="<?php e(url(null)); ?>" method="post" enctype="multipart/form-data">
            <div class="formrow">
                <label><?php e(__('File')); ?></label>
                <input id="file" type="file" name="file" onchange="upload()" />
                <input type="hidden" name="imgnum" />
            </div>
        </form>
</html>