<?php 

unset($_SESSION[$website_name]);

?><script>
localStorage.clear();
document.location.href='<?php echo $admin_url ?>login';
</script>