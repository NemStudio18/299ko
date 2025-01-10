<section>
    <header><?php echo lang::get('users-edit'); ?></header>
    <form method="POST" action="<?php $this->_show_var('link'); ?>">
        <?php $this->_show_var('SHOW.tokenField'); ?>
        <input type="hidden" name="id" value="<?php $this->_show_var('user.id'); ?>" />
        <label for="email"><?php echo lang::get('users-mail'); ?></label>
        <input type="email" id="email" name="email" value="<?php $this->_show_var('user.email'); ?>" required />
        <label for="pseudo"><?php echo lang::get('pseudo'); ?></label>
        <input type="text" id="pseudo" name="pseudo" value="<?php $this->_show_var('user.pseudo'); ?>" required />
        <label for="pwd"><?php echo lang::get('password'); ?></label>
        <input type="text" id="pwd" name="pwd" />
        <label for="role"><?php echo lang::get('users-role'); ?></label>
        <select id="role" name="role">
            <option value="admin" <?php if ($this->_show_var('user.role') == 'admin') echo 'selected'; ?>>Admin</option>
            <option value="modo" <?php if ($this->_show_var('user.role') == 'modo') echo 'selected'; ?>>Modo</option>
            <option value="editor" <?php if ($this->_show_var('user.role') == 'editor') echo 'selected'; ?>>Rédacteur</option>
            <option value="member" <?php if ($this->_show_var('user.role') == 'member') echo 'selected'; ?>>Membre</option>
        </select>
        
        <button><?php echo lang::get('submit'); ?></button>
    </form>
</section>