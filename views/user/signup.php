<?php
     
    use yii\helpers\Html;
    use yii\bootstrap\ActiveForm;
    use app\components\InitController;
    use kartik\date\DatePicker;
use app\models\User;

    $this->title = $title;

    $this->params['breadcrumbs'][] = $title;
    ?>
    <div class="site-signup">
        <h1><?= Html::encode($this->title) ?></h1>
        <p>Пожалуйста, заполните следующие поля:</p>
        <div class="row">
            <div class="col-lg-5">
     
                <?php $form = ActiveForm::begin(['id' => 'form-signup']); ?>
                    <?= $form->field($model, 'fio') ?>
                    <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>
                    <?= $form->field($model, 'role')->dropDownList([
                            User::ROLE_ADMIN => User::ROLE_ADMIN,
                            User::ROLE_EDITOR => User::ROLE_EDITOR,
                            User::ROLE_USER => User::ROLE_USER
                    ]); ?>
                    <?= $form->field($model, 'password')->passwordInput() ?>
                    <?= $form->field($model, 'info') ?>

                <div class="form-group">
                        <?= Html::submitButton($title, ['class' => 'btn btn-primary', 'name' => 'signup-button']) ?>
                    </div>
                <?php ActiveForm::end(); ?>
     
            </div>
        </div>
    </div>