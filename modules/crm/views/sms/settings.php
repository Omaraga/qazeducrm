<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Organizations $org */
/** @var array $providers */

$this->title = 'Настройки SMS';
$this->params['breadcrumbs'][] = ['label' => 'SMS уведомления', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="sms-settings">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= Html::encode($this->title) ?></h1>
        <a href="<?= Url::to(['index']) ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left"></i> Назад
        </a>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Настройки провайдера</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                        <div class="mb-3">
                            <label class="form-label">SMS провайдер</label>
                            <select name="sms_provider" class="form-select" id="sms-provider">
                                <option value="">-- Выберите провайдера --</option>
                                <?php foreach ($providers as $code => $name): ?>
                                    <option value="<?= $code ?>" <?= $org->sms_provider === $code ? 'selected' : '' ?>>
                                        <?= Html::encode($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Выберите провайдера для отправки SMS</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">API ключ</label>
                            <input type="text" name="sms_api_key" class="form-control"
                                   value="<?= Html::encode($org->sms_api_key) ?>"
                                   placeholder="Введите API ключ от провайдера">
                            <div class="form-text">API ключ можно получить в личном кабинете провайдера</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Имя отправителя</label>
                            <input type="text" name="sms_sender" class="form-control"
                                   value="<?= Html::encode($org->sms_sender) ?>"
                                   placeholder="Например: MySchool" maxlength="11">
                            <div class="form-text">До 11 латинских символов. Должно быть зарегистрировано у провайдера</div>
                        </div>

                        <?php if ($org->sms_balance !== null): ?>
                            <div class="alert alert-info">
                                <i class="fas fa-wallet"></i>
                                Баланс: <strong><?= number_format($org->sms_balance, 2, ',', ' ') ?> KZT</strong>
                            </div>
                        <?php endif; ?>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Сохранить настройки
                        </button>
                    </form>
                </div>
            </div>

            <?php if ($org->sms_provider && $org->sms_api_key): ?>
                <div class="card mt-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Тестовая отправка</h5>
                    </div>
                    <div class="card-body">
                        <form method="post" action="<?= Url::to(['test-send']) ?>">
                            <?= Html::hiddenInput(Yii::$app->request->csrfParam, Yii::$app->request->csrfToken) ?>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Номер телефона</label>
                                        <input type="text" name="phone" class="form-control"
                                               placeholder="+77001234567" required>
                                    </div>
                                </div>
                                <div class="col-md-8">
                                    <div class="mb-3">
                                        <label class="form-label">Текст сообщения</label>
                                        <input type="text" name="message" class="form-control"
                                               placeholder="Тестовое сообщение" required maxlength="160">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-outline-primary">
                                <i class="fas fa-paper-plane"></i> Отправить тестовое SMS
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Провайдеры SMS</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3 pb-3 border-bottom">
                        <h6 class="mb-1">Mobizon</h6>
                        <p class="text-muted small mb-2">Международный провайдер SMS. Работает в Казахстане.</p>
                        <a href="https://mobizon.kz" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-external-link-alt"></i> Перейти
                        </a>
                    </div>

                    <div class="mb-3 pb-3 border-bottom">
                        <h6 class="mb-1">SMS.kz</h6>
                        <p class="text-muted small mb-2">Казахстанский провайдер SMS рассылок.</p>
                        <a href="https://sms.kz" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-external-link-alt"></i> Перейти
                        </a>
                    </div>

                    <div>
                        <h6 class="mb-1">Тестовый режим</h6>
                        <p class="text-muted small mb-0">
                            Для отладки. SMS записываются в лог, но не отправляются.
                        </p>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="card-title mb-0">Автоматические уведомления</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small">
                        Для автоматической отправки SMS добавьте в cron:
                    </p>
                    <pre class="bg-light p-2 rounded small mb-2"><code># Напоминание о занятиях (18:00)
0 18 * * * php yii sms/lesson-reminder

# Задолженность (по понедельникам)
0 10 * * 1 php yii sms/payment-due

# День рождения (9:00)
0 9 * * * php yii sms/birthday</code></pre>
                    <p class="text-muted small mb-0">
                        Настройте шаблоны в разделе
                        <a href="<?= Url::to(['templates']) ?>">Шаблоны</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
