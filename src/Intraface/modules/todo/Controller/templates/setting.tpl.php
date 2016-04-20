<h1>Indstillinger</h1>

<form action="<?php e($_SERVER['PHP_SELF']); ?>" method="post">

  <fieldset>
    <label>
            <span class="labelText">Offentlig liste</span>
      <input type="text" size="60" name="publiclist" value="<?php e($value['publiclist']); ?>" />
    </label>
    <label>
            <span class="labelText">Standardtekst på e-mails</span>
      <textarea name="emailstandardtext" rows="6" cols="80"><?php e($value['emailstandardtext']); ?></textarea>
    </label>
  </fieldset>

  <input type="submit" value="Gem" />

</form>
