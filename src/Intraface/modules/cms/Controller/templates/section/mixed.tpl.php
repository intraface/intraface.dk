                    <fieldset>
                        <legend><?php e($section->get('section_name')); ?></legend>
                        <p><?php e(t('There is a html section on the page')); ?></p>
                        <input type="submit" value="<?php e(t('edit section')); ?>" name="edit_html[<?php e($section->get('id')); ?>]" />
