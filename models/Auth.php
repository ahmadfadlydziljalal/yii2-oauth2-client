<?php

namespace app\models;

use Yii;
use \app\models\base\Auth as BaseAuth;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "auth".
 */
class Auth extends BaseAuth
{

    public function behaviors()
    {
        return ArrayHelper::merge(
            parent::behaviors(),
            [
                # custom behaviors
            ]
        );
    }

    public function rules()
    {
        return ArrayHelper::merge(
            parent::rules(),
            [
                # custom validation rules
            ]
        );
    }
}
