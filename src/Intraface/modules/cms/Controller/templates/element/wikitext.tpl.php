        <fieldset>
            <legend><?php e(t('html text')); ?></legend>
            <label for="cms-wiki-editor"><?php e(t('wiki text')); ?></label>
            <br />
            <textarea id="cms-wiki-editor" tabindex="1" name="text" cols="100" rows="15" style="width: 100%"><?php if (!empty($value['text'])) {
                e($value['text']);
} ?></textarea>
        </fieldset>
