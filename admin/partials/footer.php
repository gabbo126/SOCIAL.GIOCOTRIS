        </div>
    </main>
    <footer class="admin-footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> Social Gioco Tris - Area Riservata</p>
    <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true): ?>
</div> <!-- chiude .content -->
<?php endif; ?>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
