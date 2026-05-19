<?php if (!defined('APP_RUNNING')) die('Direct access not allowed.'); ?>

</main>  <!-- close main-content -->
</div>   <!-- close admin-wrapper -->

<footer class="footer">
    <p>&copy; <?= date('Y') ?> Task Management Tool — Admin Panel</p>
</footer>

<!-- Chart.js library (loaded BEFORE app.js) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<!-- Our app JS -->
<script src="<?= BASE_URL ?>public/js/app.js"></script>
</body>
</html>