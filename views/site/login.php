<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Вход на сайт';
$this->params['breadcrumbs'][] = $this->title;
?>
<script>
    var time;
    intervalId = setInterval(timerDecrement, 1000);

    function timerDecrement() {
        const newTime = time.text() - 1;
        time.html(newTime);

        if(newTime === 0) clearInterval(intervalId);
    }

    function getTelegramCode(){
        $.ajax({
            type: 'GET',
            url: '/site/telegram-code',
            success: function (code) {
                $(".login-switch").hide();
                $(".site-login-telegram").html("Введите в <strong>Сенькин бот</strong> в Telegram этот код: <strong style='color: #e96200;'>"+code+"</strong>");

                time = $('.seconds');
                $(".timer").show();

                $.ajax({
                    url: '/site/telegram-login',
                    data: { 'code' : code},
                    success: function (html) {
                        document.location.href = '/site/login';
                    }
                })
            }
        })
    }

    $(function(){
        $(".site-login-telegram").on('click', function(){
            $(this).off('click');
            getTelegramCode();
        });

        $(".login-switch").off('click').on("click", function(){
            $(".login-panel").toggle();
        })
    })
</script>

<div class="site-login">
    <div class="login-panel" style="display: none;">
        <h1><?= Html::encode($this->title) ?></h1>

        <div id="login-form-wrap">
            <p>Заполните следующие поля для входа:</p>

            <?php $form = ActiveForm::begin([
                'id' => 'login-form',
                'layout' => 'horizontal',
                'fieldConfig' => [
                    'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
                    'labelOptions' => ['class' => 'col-lg-1 control-label'],
                ],
            ]); ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'password')->passwordInput() ?>

                <?= $form->field($model, 'rememberMe')->checkbox([
                    'template' => "<div class=\"col-lg-offset-1 col-lg-3\">{input} {label}</div>\n<div class=\"col-lg-8\">{error}</div>",
                ]) ?>

                <div class="form-group">
                    <div class="col-lg-offset-1 col-lg-11">
                        <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                    </div>
                </div>

            <?php ActiveForm::end(); ?>
        </div>
        <a class="login-switch">Войти через Telergam →</a>

    </div>

    <div class="login-panel">
        <h1>Войти через Telergam</h1>
        <div class="site-login-telegram">
            <a href="#" style="color: #e96200;">Получить код</a>
        </div>
        <div class="timer" style="display: none; color: #333;">Код действует еще: <span class="seconds">60</span> сек</div>
        <a class="login-switch">Войти через логин/пароль →</a>
    </div>
</div>

<style>
    .login-switch {
        display: inline-block;
        margin: 20px 0;
    }
    .site-login-telegram{
        font-size: 1.3em;
    }
</style>