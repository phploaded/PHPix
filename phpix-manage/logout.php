<?php 

unset($_SESSION['PHPix']);
unset($_SESSION['phpixuser']);

?><script>
localStorage.clear();
document.location.href='<?php echo $domain.''.$albumFILE; ?>';
</script>