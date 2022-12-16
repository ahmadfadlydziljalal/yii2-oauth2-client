<?php

namespace app\models\active_query;

/**
 * This is the ActiveQuery class for [[\app\models\Auth]].
 *
 * @see \app\models\Auth
 */
class AuthQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        $this->andWhere('[[status]]=1');
        return $this;
    }*/

    /**
     * @inheritdoc
     * @return \app\models\Auth[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return \app\models\Auth|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
