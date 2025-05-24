<div class="pagination">
    <?php if ($currentPage > 1): ?>
        <a href="?page=1&per_page=<?= $perPage ?>&order_by=<?= $orderBy ?>&order_dir=<?= $orderDir ?>">Перша</a>
        <a href="?page=<?= $currentPage-1 ?>&per_page=<?= $perPage ?>&order_by=<?= $orderBy ?>&order_dir=<?= $orderDir ?>">‹</a>
    <?php endif; ?>

    <?php for ($i = max(1, $currentPage-2); $i <= min($totalPages, $currentPage+2); $i++): ?>
        <a href="?page=<?= $i ?>&per_page=<?= $perPage ?>&order_by=<?= $orderBy ?>&order_dir=<?= $orderDir ?>"
           class="<?= $i == $currentPage ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>

    <?php if ($currentPage < $totalPages): ?>
        <a href="?page=<?= $currentPage+1 ?>&per_page=<?= $perPage ?>&order_by=<?= $orderBy ?>&order_dir=<?= $orderDir ?>">›</a>
        <a href="?page=<?= $totalPages ?>&per_page=<?= $perPage ?>&order_by=<?= $orderBy ?>&order_dir=<?= $orderDir ?>">Остання</a>
    <?php endif; ?>
</div>