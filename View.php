<?php

use backend\widgets\CategoryTree;
use backend\widgets\CustomUpload;
use backend\widgets\PropertiesWidget;
use common\helpers\MultilingualHelper;
use trntv\filekit\widget\Upload;
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\imperavi\Widget;
use yiiui\yii2flagiconcss\widget\FlagIcon;
use kartik\select2\Select2;

/**
 * @var $this yii\web\View
 * @var $model common\models\Product
 * @var $categories []
 * @var $products []
 * @var $units []
 * @var $languages []
 * @var $selectedCategories []
 */

?>

<?php $form = ActiveForm::begin([
    'enableClientValidation' => false,
    'enableAjaxValidation' => true,
]); ?>

<div class="row">
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?= Yii::t('backend', 'General') ?>
                </h3>
            </div>
            <div class="box-body">
                <?= $form->field($model, 'slug')
                    ->hint(Yii::t('backend', 'If you leave this field empty, the slug will be generated automatically'))
                    ->textInput(['maxlength' => 255]) ?>

                <?= $form->field($model, 'code')->textInput(['maxlength' => 32]) ?>

                <?= $form->field($model, 'source_id')->textInput(['maxlength' => 255]) ?>

                <?= $form->field($model, 'main_category_id')->dropDownList($categories) ?>

                <?= $form->field($model, 'is_active')->checkbox() ?>

                <?= $form->field($model, 'is_service')->checkbox() ?>

                <?= $form->field($model, 'price') ?>

                <?= $form->field($model, 'weight') ?>

                <?= $form->field($model, 'unit_id')->dropDownList($units) ?>


            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?= $model->getAttributeLabel('title') ?>
                </h3>
            </div>
            <div class="box-body">
                <?php foreach ($languages as $key => $language) : ?>
                    <?= $form->field($model, MultilingualHelper::getFieldName('title', $key), [
                        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\">" . FlagIcon::widget([
                                'countryCode' => MultilingualHelper::getLanguageISOName($key),
                            ]) . "</span>{input}</div>\n{hint}\n{error}"
                    ])
                        ->textInput(['maxlength' => 255])->label(false) ?>
                <?php endforeach ?>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?= $model->getAttributeLabel('seo_title') ?>
                </h3>
            </div>
            <div class="box-body">
                <?php foreach ($languages as $key => $language) : ?>
                    <?= $form->field($model, MultilingualHelper::getFieldName('seo_title', $key), [
                        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\">" . FlagIcon::widget([
                                'countryCode' => MultilingualHelper::getLanguageISOName($key),
                            ]) . "</span>{input}</div>\n{hint}\n{error}"
                    ])
                        ->textInput(['maxlength' => 255])->label(false) ?>
                <?php endforeach ?>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?= $model->getAttributeLabel('seo_description') ?>
                </h3>
            </div>
            <div class="box-body">
                <?php foreach ($languages as $key => $language) : ?>
                    <?= $form->field($model, MultilingualHelper::getFieldName('seo_description', $key), [
                        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\">" . FlagIcon::widget([
                                'countryCode' => MultilingualHelper::getLanguageISOName($key),
                            ]) . "</span>{input}</div>\n{hint}\n{error}"
                    ])
                        ->textarea(['maxlength' => 512])->label(false) ?>
                <?php endforeach ?>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?= Yii::t('backend', 'Categories') ?>
                </h3>
            </div>
            <div class="box-body">
                <?= CategoryTree::widget([
                    'selected' => $selectedCategories
                ]) ?>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?= Yii::t('backend', 'Properties') ?>
                </h3>
            </div>
            <div class="box-body">
                <?= PropertiesWidget::widget([
                    'model' => $model,
                ]) ?>
            </div>
        </div>
        <div class="box box-primary">
            <?php echo $form->field($model, 'attachments')->widget(CustomUpload::class, ['url' => ['/file/storage/upload'],                'sortable' => true,
                'maxFileSize' => 10000000, // 10 MiB
                'maxNumberOfFiles' => 99,]);
            ?>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?= Yii::t('backend', 'Related products') ?>
                </h3>
            </div>
            <div class="box-body">
                <?= $form->field($model, 'relatedProducts')->widget(Select2::class, [
                    'data' => $products,
                    'maintainOrder' => true,
                    'options' => ['placeholder' => Yii::t('backend', 'Related products'), 'multiple' => true],
                    'pluginOptions' => [
                        'tags' => true,
                        'maximumInputLength' => 20,
                    ],
                ])->label(false) ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?= $model->getAttributeLabel('content') ?>
                </h3>
            </div>
            <div class="box-body">
                <?php foreach ($languages as $key => $language) : ?>
                    <?= $form->field($model, MultilingualHelper::getFieldName('content', $key), [
                        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\">" . FlagIcon::widget([
                                'countryCode' => MultilingualHelper::getLanguageISOName($key),
                            ]) . "</span>{input}</div>\n{hint}\n{error}"
                    ])
                        ->widget(
                            Widget::class,
                            [
                                'plugins' => ['fullscreen', 'fontcolor', 'video'],
                                'options' => [
                                    'minHeight' => 400,
                                    'maxHeight' => 400,
                                    'buttonSource' => true,
                                    'imageUpload' => Yii::$app->urlManager->createUrl(['/file/storage/upload-imperavi']),
                                ],
                            ]
                        )->label(false) ?>
                <?php endforeach ?>
            </div>
        </div>
        <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">
                    <?= $model->getAttributeLabel('announce') ?>
                </h3>
            </div>
            <div class="box-body">
                <?php foreach ($languages as $key => $language) : ?>
                    <?= $form->field($model, MultilingualHelper::getFieldName('announce', $key), [
                        'template' => "{label}\n<div class=\"input-group\"><span class=\"input-group-addon\">" . FlagIcon::widget([
                                'countryCode' => MultilingualHelper::getLanguageISOName($key),
                            ]) . "</span>{input}</div>\n{hint}\n{error}"
                    ])
                        ->widget(
                            Widget::class,
                            [
                                'plugins' => ['fullscreen', 'fontcolor', 'video'],
                                'options' => [
                                    'minHeight' => 400,
                                    'maxHeight' => 400,
                                    'buttonSource' => true,
                                    'imageUpload' => Yii::$app->urlManager->createUrl(['/file/storage/upload-imperavi']),
                                ],
                            ]
                        )->label(false) ?>
                <?php endforeach ?>
            </div>
        </div>
    </div>
</div>

<div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('backend', 'Create') : Yii::t('backend', 'Update'), [
        'class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary'
    ]) ?>
</div>

<?php ActiveForm::end() ?>
