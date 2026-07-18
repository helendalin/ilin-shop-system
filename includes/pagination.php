<?php
function renderPagination($currentPage, $totalPages, $baseUrl, $queryParams = []) {
    if ($totalPages <= 1) {
        return;
    }

    echo '<div class="pagination">';

    if ($currentPage > 1) {
        $queryParams['page'] = $currentPage - 1;
        echo '<a href="' . $baseUrl . '?' . http_build_query($queryParams) . '">« Previous</a>';
    }

    for ($i = 1; $i <= $totalPages; $i++) {
        $queryParams['page'] = $i;

        $activeClass = ($i == $currentPage) ? 'active' : '';

        echo '<a class="' . $activeClass . '" href="' . $baseUrl . '?' . http_build_query($queryParams) . '">' . $i . '</a>';
    }

    if ($currentPage < $totalPages) {
        $queryParams['page'] = $currentPage + 1;
        echo '<a href="' . $baseUrl . '?' . http_build_query($queryParams) . '">Next Page »</a>';
    }

    echo '</div>';
}
?>