<?php


namespace app\models\services;


use app\models\Payment;
use app\models\Pupil;
use app\models\PupilEducation;

class PupilService
{
    public static function updateBalance(int $pupilId){
        $pupil = Pupil::findOne($pupilId);
        $payments = Payment::find()->andWhere(['pupil_id' => $pupil->id])->byOrganization()->orderBy('date ASC')->asArray()->notDeleted()->all();
        $balance = 0;
        foreach ($payments as $payment){
            if ($payment['type'] == Payment::TYPE_PAY){
                $balance += $payment['amount'];
            }else{
                $balance -= $payment['amount'];
            }
        }
        $pupilEducations = PupilEducation::find()->where(['pupil_id' => $pupil->id])->byOrganization()->notDeleted()->asArray()->all();
        foreach ($pupilEducations as $education){
            $balance -= $education['total_price'];
        }
        $pupil->balance = $balance;
        $pupil->save(false);
    }

}