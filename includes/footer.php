    </div> <!-- penutup container -->
    
    <!-- jQuery dulu, baru Bootstrap, baru yang lain -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    
    <?php 
    $base_url = base_url(); 
    ?>
    
    <script>
        // Base URL untuk JavaScript - didefinisikan SEBELUM file lain
        var baseUrl = '<?php echo $base_url; ?>';
        console.log('Base URL:', baseUrl);
        console.log('jQuery version:', $.fn.jquery);
    </script>
    
    <!-- File JS kita setelah semua library -->
    <script src="<?php echo $base_url; ?>/assets/js/main.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/notifications.js"></script>
    
    <?php if (isset($additional_scripts)) echo $additional_scripts; ?>
</body>
</html>