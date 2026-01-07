<?php

namespace app\models\services;

use app\models\Payment;
use app\models\Pupil;
use app\models\PupilEducation;
use Yii;

class PupilService
{
    /**
     * Пересчитывает баланс ученика на основе платежей и обучений
     *
     * @param int $pupilId ID ученика
     * @return bool true если успешно, false при ошибке
     */
    public static function updateBalance(int $pupilId): bool
    {
        // Транзакция для защиты от race condition
        $transaction = Yii::$app->db->beginTransaction();

        try {
            $pupil = Pupil::findOne($pupilId);

            if ($pupil === null) {
                $transaction->rollBack();
                Yii::error("PupilService::updateBalance - Pupil not found: {$pupilId}", 'application');
                return false;
            }

            // Считаем сумму платежей
            $payments = Payment::find()
                ->andWhere(['pupil_id' => $pupil->id])
                ->byOrganization()
                ->notDeleted()
                ->orderBy(['date' => SORT_ASC])
                ->asArray()
                ->all();

            $balance = 0;
            foreach ($payments as $payment) {
                // Строгое сравнение типов
                if ((int)$payment['type'] === Payment::TYPE_PAY) {
                    $balance += (float)$payment['amount'];
                } else {
                    $balance -= (float)$payment['amount'];
                }
            }

            // Вычитаем стоимость обучений
            $pupilEducations = PupilEducation::find()
                ->where(['pupil_id' => $pupil->id])
                ->byOrganization()
                ->notDeleted()
                ->asArray()
                ->all();

            foreach ($pupilEducations as $education) {
                $balance -= (float)$education['total_price'];
            }

            $pupil->balance = $balance;

            if (!$pupil->save(false, ['balance', 'updated_at'])) {
                $transaction->rollBack();
                Yii::error([
                    'message' => 'PupilService::updateBalance - Failed to save pupil balance',
                    'pupil_id' => $pupilId,
                    'balance' => $balance,
                    'errors' => $pupil->errors,
                ], 'application');
                return false;
            }

            $transaction->commit();
            return true;
        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error([
                'message' => 'PupilService::updateBalance - Exception',
                'pupil_id' => $pupilId,
                'error' => $e->getMessage(),
            ], 'application');
            return false;
        }
    }
}
