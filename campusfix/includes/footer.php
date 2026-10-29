</div> 

<footer class="site-footer">
    <div class="container">
        <div class="row">
            <div class="col-lg-4 mb-4 mb-lg-0">
                <h5>CampusFix</h5>
                <p class="footer-description">
                    A comprehensive Complaint Management System designed to streamline maintenance and infrastructure reporting for a cleaner, safer campus environment.
                </p>
            </div>
            
            <div class="col-lg-2 col-md-4 mb-4 mb-md-0">
                <h5>Quick Links</h5>
                <ul class="list-unstyled">
                    <li><a href="<?= BASE_URL ?>/track_complaint.php" class="footer-link">Track Complaint</a></li>
                    <?php if (isset($_SESSION['role'])): ?>
                        <li><a href="<?= redirectByRole($_SESSION['role']) ?>" class="footer-link">Dashboard</a></li>
                    <?php else: ?>
                        <li><a href="<?= BASE_URL ?>/login.php" class="footer-link">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-4 mb-4 mb-md-0">
                <h5>Categories</h5>
                <ul class="list-unstyled">
                    <li><span class="footer-description">Electrical</span></li>
                    <li><span class="footer-description">Plumbing</span></li>
                    <li><span class="footer-description">Infrastructure</span></li>
                </ul>
            </div>
            
            <div class="col-lg-3 col-md-4">
                <h5>Contact Support</h5>
                <ul class="list-unstyled">
                    <li class="footer-description mb-2">admin@campusfix.com</li>
                    <li class="footer-description mb-2">+91 9974716620</li>
                    <li class="footer-description">Main Campus Building, Room 101</li>
                </ul>
            </div>
        </div>
        
        <div class="footer-divider"></div>
        
        <div class="row align-items-center footer-bottom">
            <div class="col-md-6 text-center text-md-start mb-2 mb-md-0">
                &copy; <?= date('Y') ?> CampusFix. All rights reserved.
            </div>
            <div class="col-md-6 text-center text-md-end">
                Campus Facility Management Portal
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
