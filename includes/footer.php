<footer id="contact">
        <div class="container footer-content">
            <div class="footer-col">
                <a href="#" class="logo text-white">Villa<span>Batu</span>.</a>
                <p>Solusi penginapan terbaik di Kota Wisata Batu.</p>
            </div>
            </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> VillaBatu Exclusive.</p>
        </div>
    </footer>
    
    <script>
        // Sticky Navbar Script
        window.addEventListener('scroll', function() {
            const navbar = document.querySelector('.navbar');
            if(navbar) navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
    </script>
</body>
</html>
