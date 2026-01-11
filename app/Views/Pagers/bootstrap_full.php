<?php
$pager->setSurroundCount(2);

// Mevcut query string'i al (page hariç)
$query = $_GET ?? [];
foreach (array_keys($query) as $k) {
    if ($k === 'page' || str_starts_with($k, 'page_')) {
        unset($query[$k]);
    }
}

$qs = '';
if (! empty($query)) {
    $qs = '&' . http_build_query($query);
}

// Linklere güvenli şekilde ekleyen küçük helper
$append = function (string $url) use ($qs): string {
    if ($qs === '') return $url;
    return (strpos($url, '?') !== false) ? ($url . $qs) : ($url . '?' . ltrim($qs, '&'));
};
?>

<?php if ($pager->getPageCount() > 1): ?>
<nav aria-label="Sayfalama">
    <ul class="pagination mb-0 justify-content-center">

        <?php if ($pager->hasPrevious()): ?>
            <li class="page-item">
                <a class="page-link" href="<?= esc($append($pager->getFirst())) ?>" aria-label="İlk">&laquo;</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= esc($append($pager->getPrevious())) ?>" aria-label="Önceki">&lsaquo;</a>
            </li>
        <?php else: ?>
            <li class="page-item disabled"><span class="page-link">&laquo;</span></li>
            <li class="page-item disabled"><span class="page-link">&lsaquo;</span></li>
        <?php endif; ?>

        <?php foreach ($pager->links() as $link): ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <a class="page-link" href="<?= esc($append($link['uri'])) ?>">
                    <?= esc($link['title']) ?>
                </a>
            </li>
        <?php endforeach; ?>

        <?php if ($pager->hasNext()): ?>
            <li class="page-item">
                <a class="page-link" href="<?= esc($append($pager->getNext())) ?>" aria-label="Sonraki">&rsaquo;</a>
            </li>
            <li class="page-item">
                <a class="page-link" href="<?= esc($append($pager->getLast())) ?>" aria-label="Son">&raquo;</a>
            </li>
        <?php else: ?>
            <li class="page-item disabled"><span class="page-link">&rsaquo;</span></li>
            <li class="page-item disabled"><span class="page-link">&raquo;</span></li>
        <?php endif; ?>

    </ul>
</nav>
<?php endif; ?>
