<?php
/**
 * includes/header.php
 * Shared <head> + opening <body> for PUBLIC pages only.
 * Expects $pageTitle to be set by the including page.
 */
if (!isset($pageTitle)) {
    $pageTitle = SCHOOL_NAME;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= e($pageTitle) ?> | <?= e(SCHOOL_NAME) ?></title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css?v=<?= ASSET_VERSION ?>">
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/public.css?v=<?= ASSET_VERSION ?>">
</head>
<body>
<?php include __DIR__ . '/navbar.php'; ?>
