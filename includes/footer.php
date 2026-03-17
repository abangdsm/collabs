    </div> <!-- penutup container dari content-wrapper -->
    
    <!-- Copyright Footer -->
    <footer class="footer mt-auto py-4 bg-light border-top">
        <div class="container text-center">
            <div class="row">
                <div class="col-12">
                    <p class="mb-1">
                        &copy; Dwi Star Muda <?php echo date('Y'); ?> All right reserved | <strong class="text-dark">Collabs</strong> <small>v1.0.0</small> - Team Collaborator Platform
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- SEMUA SCRIPT DILETAKKAN DI SINI -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.min.js"></script>
    
    <?php 
    $base_url = base_url(); 
    ?>
    
    <script>
        var baseUrl = '<?php echo $base_url; ?>';
        console.log('Base URL:', baseUrl);
        console.log('jQuery version:', jQuery.fn.jquery);
    </script>
    
    <script src="<?php echo $base_url; ?>/assets/js/main.js"></script>
    <script src="<?php echo $base_url; ?>/assets/js/notifications.js"></script>
    
    <?php if (isset($additional_scripts)) echo $additional_scripts; ?>
</body>
</html>