<h1>Intraface Developer Tools</h1>

<?php if(!empty($error_message)): ?>
    <p class="message"><?php e($error_message); ?></p>
<?php endif; ?>

<form method="post" action="<?php e(url('.')); ?>">

<p>
    <label for="username">Username</label>
    <input type='text' name='username' id='username' value='' />
</p>
<p>
    <label for="Password">Password</label>
    <input type='password' name='password' id='password' value='' />
</p>

<p>
  <input type="submit" name="send" value="Login" />
</p>
</form>
