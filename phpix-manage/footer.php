</div><!-- /#page-wrapper -->
</div><!-- /#wrapper -->
<div style="padding:10px;" class="text-center">A product of <a href="http://phploaded.com/project/phpix.html" target="_blank" class="text-danger">phploaded.com</a></div>



<script>

var gal_brightness_elements = '.dropzone .dz-preview .dz-image img, .mlib-thumbs, .rcrop-preview-wrapper canvas, #rotate-preview-img, .album-thumb';

jQuery(document).ready(function(){
var curl = window.location;
$('#bigmenu a[href="'+curl+'"]').closest('li').addClass('active');

jQuery('body').on('keyup', null, 'shift+b', function(){
gal_brightness();
});

});
</script>
</body>

</html>
