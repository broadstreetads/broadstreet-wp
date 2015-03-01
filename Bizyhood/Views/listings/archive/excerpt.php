<style>    
    .sponsored-listing {
        background: rgb(254,252,234); /* Old browsers */
        background: -moz-linear-gradient(top,  rgba(254,252,234,1) 0%, rgba(241,218,54,1) 100%); /* FF3.6+ */
        background: -webkit-gradient(linear, left top, left bottom, color-stop(0%,rgba(254,252,234,1)), color-stop(100%,rgba(241,218,54,1))); /* Chrome,Safari4+ */
        background: -webkit-linear-gradient(top,  rgba(254,252,234,1) 0%,rgba(241,218,54,1) 100%); /* Chrome10+,Safari5.1+ */
        background: -o-linear-gradient(top,  rgba(254,252,234,1) 0%,rgba(241,218,54,1) 100%); /* Opera 11.10+ */
        background: -ms-linear-gradient(top,  rgba(254,252,234,1) 0%,rgba(241,218,54,1) 100%); /* IE10+ */
        background: linear-gradient(to bottom,  rgba(254,252,234,1) 0%,rgba(241,218,54,1) 100%); /* W3C */
        filter: progid:DXImageTransform.Microsoft.gradient( startColorstr='#fefcea', endColorstr='#f1da36',GradientType=0 ); /* IE6-9 */
        padding: 5px;
        margin-bottom: 10px;
        border-radius: 3px;
        border: 1px solid #ddd;
        text-align: center;
        margin-left: 10px;
        clear:both;
    }
    
</style>

<?php echo $content ?>
<?php if($meta['bs_update_source']): ?>
    <div class="sponsored-listing">
        <?php echo $meta['bs_advertisement_html'] ?>
    </div>
<?php endif; ?>