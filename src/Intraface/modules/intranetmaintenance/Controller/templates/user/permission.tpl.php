
    <form action="<?php e(url(null, array('intranet_id' => $context->query('intranet_id')))); ?>" method="post">

    <fieldset>
        <legend>Access to intranet</legend>
        <div>
            <input type="checkbox" name="intranetaccess" id="intranetaccess" value="1" <?php if ($context->getUser()->hasIntranetAccess()) print("checked=\"checked\""); ?> />
            <label for="intranetaccess">Adgang til intranettet</label>
        </div>
    </fieldset>

    <fieldset>
        <legend>Adgang til moduler</legend>
        <?php
        $module = new ModuleMaintenance;
        $modules = $module->getList();

        for ($i = 0; $i < count($modules); $i++) {
            if ($context->getIntranet()->hasModuleAccess(intval($modules[$i]["id"]))) {
                ?>
                <div>
                    <input type="checkbox" name="module[]" id="module_<?php e($modules[$i]["name"]); ?>" value="<?php e($modules[$i]["name"]); ?>" <?php if ($context->getUser()->hasModuleAccess(intval($modules[$i]["id"]))) print("checked=\"checked\""); ?> />
                    <label for="module_<?php e($modules[$i]["name"]); ?>"><?php e($modules[$i]["menu_label"]); ?></label>
                    <?php if (!empty($modules[$i]["sub_access"]) AND count($modules[$i]["sub_access"]) > 0): ?>
                      <ol>
                      <?php for ($j = 0; $j < count($modules[$i]["sub_access"]); $j++): ?>
                          <li><input type="checkbox" name="sub_access[<?php e($modules[$i]["name"]); ?>][]" id="sub_<?php e($modules[$i]["sub_access"][$j]["name"]); ?>" value="<?php e($modules[$i]["sub_access"][$j]["name"]); ?>"<?php if ($context->getUser()->hasSubAccess(intval($modules[$i]["id"]), intval($modules[$i]["sub_access"][$j]["id"]))) print(" checked=\"checked\""); ?> />
                          <label for="sub_<?php e($modules[$i]["sub_access"][$j]["name"]); ?>"><?php e($modules[$i]["sub_access"][$j]["description"]); ?></label></li>
                      <?php endfor; ?>
                      </ol>
                      <?php endif; ?>
                </div>
                <?php } }
        ?>
    </fieldset>

    <input type="hidden" name="id" value="<?php e($context->getUser()->get("id")); ?>" />
    <input type="hidden" name="intranet_id" value="<?php e($intranet->get("id")); ?>" />
    <input type="submit" name="submit" value="Gem" />
    </form>