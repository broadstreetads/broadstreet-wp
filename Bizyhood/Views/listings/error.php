<div class="row bh_business-header">
    <div class="col-md-8 bh_business-header-title">	
      <?php if (!(isset($noheader) && $noheader === true)) { ?>
        <h3>There seems to be a problem!</h3>
      <?php } ?>
        <div class="bh_panel bh_alert">
          <p>
          <?php echo $error; ?>
          </p>
        </div>
    </div>
    
</div>
