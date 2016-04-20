
    <form action="<?php e(url(null, array('intranet_id' => $context->query('intranet_id')))); ?>" method="post">

    <fieldset>
        <legend>Access to intranet</legend>
        <div>
            <input type="checkbox" name="intranetaccess" id="intranetaccess" value="1" <?php if ($context->getUser()->hasIntranetAccess()) {
                print("checked=\"checked\"");
} ?> />
            <label for="intranetaccess">Adgang til intranettet</label>
        </div>
    </fieldset>

    <fieldset>
        <legend>Adgang til moduler</legend>
        <?php
        $module = new ModuleMaintenance;
        $modules = $module->getList();

        foreach ($modules as $module) {
            if ($context->getIntranet()->hasModuleAccess(intval($module["id"]))) {
                ?>
                <div>
                    <input type="checkbox" name="module[]" id="module_<?php e($module["name"]); ?>" value="<?php e($module["name"]); ?>" <?php if ($context->getUser()->hasModuleAccess(intval($module["id"]))) {
                        print("checked=\"checked\"");
} ?> />
                    <label for="module_<?php e($module["name"]); ?>"><?php e(t($module["name"])); ?></label>
                    <?php if (!empty($module["sub_access"]) and count($module["sub_access"]) > 0) : ?>
                      <ol>
                        <?php for ($j = 0; $j < count($module["sub_access"]); $j++) : ?>
                          <li><input type="checkbox" name="sub_access[<?php e($module["name"]); ?>][]" id="sub_<?php e($module["sub_access"][$j]["name"]); ?>" value="<?php e($module["sub_access"][$j]["name"]); ?>"<?php if ($context->getUser()->hasSubAccess(intval($module["id"]), intval($module["sub_access"][$j]["id"]))) {
                                print(" checked=\"checked\"");
} ?> />
                          <label for="sub_<?php e($module["sub_access"][$j]["name"]); ?>"><?php e(t($module["sub_access"][$j]["name"])); ?></label></li>
                        <?php endfor; ?>
                      </ol>
                        <?php endif; ?>
                </div>
                <?php                                                                                                                                                                                                                                                                                                                                     }
        }
        ?>
    </fieldset>

    <input type="hidden" name="id" value="<?php e($context->getUser()->get("id")); ?>" />
    <input type="hidden" name="intranet_id" value="<?php e($context->getIntranet()->get("id")); ?>" />
    <input type="submit" name="submit" value="Gem" />
    </form>