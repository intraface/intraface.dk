                <div class="formrow">
                    <label for="section_<?php e($section->get('id')); ?>"><?php e($section->get('section_name')); ?></label>
                    <input id="section_<?php e($section->get('id')); ?>" value="<?php e($value['section'][$section->get('id')]['text']); ?>" name="section[<?php e($section->get('id')); ?>][text]" type="text" maxlength="<?php e($section->template_section->get('size')); ?>" />
                </div>