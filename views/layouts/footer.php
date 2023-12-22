<?php
/* @var $this \yii\web\View */
?>

<footer class="footer mt-auto py-3 text-muted">
    <div class="container">
        <p class="float-left">&copy; Qazaq Education <?= date('Y') ?></p>
        <p class="float-right"><?= \Yii::t('yii', 'Powered by {yii}', [
                'yii' => '<a href="tel:87757296129" rel="external">' . \Yii::t('yii',
                        'Омар Жумагалиев') . '</a>',
            ]); ?></p>
    </div>
</footer>
