<?php
// partial/breadcrumb_local.php
$title = $GLOBALS['_page_title'] ?? 'Dashboard';
?>
<div class="container-fluid">
  <div class="page-title">
    <div class="row">
      <div class="col-6">
        <h3><?= htmlspecialchars($title) ?></h3>
      </div>
      <div class="col-6">
        <ol class="breadcrumb" style="flex-wrap: nowrap; white-space: nowrap;">
          <li class="breadcrumb-item">
            <a href="<?= BASE_URL ?>index.php">
              <svg class="stroke-icon"><use href="<?= BASE_URL ?>assets/svg/icon-sprite.svg#stroke-home"></use></svg>
            </a>
          </li>
          <li class="breadcrumb-item active"><?= htmlspecialchars($title) ?></li>
        </ol>
      </div>
    </div>
  </div>
</div>
