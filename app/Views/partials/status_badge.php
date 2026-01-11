<?php
/** @var string $status */

use App\Models\PublishModel;

$status = (string)($status ?? '');
$label  = PublishModel::label($status);
$cls    = PublishModel::badge($status);

?>
<span class="badge rounded-pill bg-<?= esc($cls) ?>">
  <?= esc($label) ?>
</span>
