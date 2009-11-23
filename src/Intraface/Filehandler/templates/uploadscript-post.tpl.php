    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
        <html xmlns="http://www.w3.org/1999/xhtml" lang="da">
            <head>
                <script type="text/javascript">
                    var par = window.parent.document;
                    var images = par.getElementById('images');
                    var imgdiv = images.getElementsByTagName('div')[<?php echo (int)$_POST['imgnum']; ?>];
                    var image = imgdiv.getElementsByTagName('img')[0];
                    imgdiv.removeChild(image);
                    var image_new = par.createElement('img');
                    image_new.width = '75';
                    image_new.height = '75';
                    var br_new = par.createElement('br');
                    <?php
                    $filemanager = new FileManager($kernel);
                    $filemanager->createUpload();
                    $filemanager->upload->setSetting('file_accessibility', 'public');
                    $filemanager->upload->setSetting('max_file_size', '10000000');
                    if ($filemanager->upload->upload('file', 'temporary')) {
                        $filemanager->load();
                        ?>
                        var input_new = par.createElement('input');
                        // input_new.setAttribute("value", <?php e($filemanager->get('id')); ?>); // Ser herunder
                        input_new.setAttribute("name", 'addfile[]');
                        input_new.setAttribute("type", 'checkbox');
                        input_new.setAttribute("id", "input-test");


                        image_new.src = '<?php e($filemanager->get('icon_uri')); ?>';
                        image_new.className = 'loaded';
                        imgdiv.appendChild(image_new);
                        imgdiv.appendChild(br_new);
                        imgdiv.appendChild(input_new);

                        /* IE HACK */
                        // IE forstår ikke set attribute, så derfor må vi gøre det uden for DOM bagefter!
                        input_new.checked = true;
                        input_new.value = <?php e($filemanager->get('id')); ?>;

                        <?php
                    } else {
                        ?>
                        image_new.src = '<?php e(url('/images/upload_error.jpg')); ?>';
                        image_new.className = 'loaded';
                        imgdiv.appendChild(image_new);
                        imgdiv.appendChild(br_new);
                        alert("<?php echo addslashes(implode(". ", $filemanager->error->getMessage())); ?>");
                        <?php
                    }
                    ?>
                </script>
            </head>
        </html>
