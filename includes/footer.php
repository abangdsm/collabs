    </div> <!-- penutup container -->
    
    <!-- SEMUA SCRIPT DILETAKKAN DI SINI -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    
    <?php 
    $base_url = base_url(); 
    ?>
    
    <script>
        // PASTIKAN INI DIEKSEKUSI SETELAH JQUERY DILOAD
        var baseUrl = '<?php echo $base_url; ?>';
        console.log('Base URL:', baseUrl);
        console.log('jQuery version:', jQuery.fn.jquery);
    </script>
    
    <!-- File JS kita -->
    <script src="<?php echo $base_url; ?>/assets/js/main.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/notifications.js"></script>
    
    <?php if (isset($additional_scripts)) echo $additional_scripts; ?>
</body>
</html>