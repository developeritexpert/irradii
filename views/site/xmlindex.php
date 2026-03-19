<?php
use yii\helpers\Html;

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>

<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">

<?php foreach ($urls as $url => $data): ?>

<sitemap>
    <loc><?= Html::encode($url) ?></loc>

    <?php if (isset($data['lastmod'])): ?>
        <lastmod><?= Html::encode($data['lastmod']) ?></lastmod>
    <?php endif; ?>

</sitemap>

<?php endforeach; ?>

</sitemapindex>