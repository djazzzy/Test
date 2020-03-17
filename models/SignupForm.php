<?php

namespace app\models;

use yii\base\Model;
use Yii;


/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string $login
 * @property string $password
 * @property string $email
 * @property string $username
 */
class SignupForm extends Model
{

//    public $login;
    public $password;
    public $password2;
    public $email;
    public $username;
    public $verifyCode;
    public $status;
    public $auth_key;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['username', 'password', 'password2', 'email'], 'required'],
            [['username', 'password', 'password2', 'email'], 'string', 'max' => 35],
            ['username', 'string', 'min' => 2, 'max' => 35],
            [['password', 'password2'],  'string', 'min' => 6, 'max' => 35],
            ['password2', 'compare', 'compareAttribute'=>'password', 'message' => 'Пароли должны совпадать.'],
            ['password', 'match', 'pattern' => '/[a-z0-9]\w*$/i', 'message' => 'Пароль должен содежрать только латинские буквы и цифры.'],
            ['username', 'unique', 'targetClass' => User::className(), 'message' => 'Это имя уже занято.'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => User::className(), 'message' => 'Эта почта уже занята.'],
            ['verifyCode', 'captcha'],
            ['status', 'default', 'value' => User::STATUS_ACTIVE, 'on' => 'default'],
            ['status', 'in', 'range' =>[
                User::STATUS_NOT_ACTIVE,
                User::STATUS_ACTIVE
            ]],
            ['status', 'default', 'value' => User::STATUS_NOT_ACTIVE, 'on' => 'emailActivation'],
        ];
    }

//    public function rules()
//    {
//        return [
//            [['password', 'login'], 'required'],
//            [['password','login'], 'string', 'max' => 35],
//            ['login', 'unique', 'targetClass' => User::className(),  'message' => 'Этот логин уже занят'],
//        ];
//    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'Логин',
            'password' => 'Пароль',
            'password2' => 'Подтвердите пароль',
            'email' => 'Email',
            'username' => 'Имя',
            'verifyCode' => 'Подтвержение действия',
        ];
    }

    public function reg()
    {
        $user = new User();
        $user->username = $this->username;
        $user->email = $this->email;
        $user->status = $this->status;
        $user->setPassword($this->password);
        $user->generateAuthKey();
        if($this->scenario === 'emailActivation')
            $user->generateSecretKey();
        return $user->save() ? $user : null;
    }

    public function sendActivationEmail($user)
    {
        return Yii::$app->mailer->compose('activationEmail', ['user' => $user])
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name.' (отправлено роботом).'])
            ->setTo($this->email)
            ->setSubject('Активация для '.Yii::$app->name)
            ->send();
    }
}
