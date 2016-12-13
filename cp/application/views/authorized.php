<?php if  (isset($error_message))  {
?>
    <div class="warning"><?php echo $error_message;?></div>
<?php
}
?>
<form action="<?php echo $auth_url;?>" method="POST">
    <table>
        <tr>
            <td>Логин:</td>
            <td><input name="username" type="text" required/></td>
        </tr>
        <tr>
            <td>Пароль:</td>
            <td><input name="password" type="password" required/></td>
        </tr>
        <tr>
            <td><input type="submit" value="Войти"/></td>
        </tr>
    </table>
</form>