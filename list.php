<?php

use common\widgets\Banner;
use common\widgets\DbCarousel;
use frontend\modules\shop\widgets\FilterWidget;
use frontend\modules\shop\widgets\FilterActiveWidget;
use common\models\Preferences;
use yii\bootstrap\Modal;
use yii\helpers\Html;
use yii\widgets\LinkPager;
use yii\widgets\Breadcrumbs;
use yii\bootstrap\Nav;
use yii\web\View;

/**
 * @var $this yii\web\View
 * @var $breadcrumbs array
 * @var $category common\models\Category
 * @var $products common\models\Product[]
 * @var $preferences common\models\Preferences
 * @var $pages yii\data\Pagination
 */

$this->params['breadcrumbs'] = $breadcrumbs;

$this->registerJsFile('@frontendUrl/slick/slick.min.js',
    ['position' =>View::POS_END,'depends' =>[\yii\web\JqueryAsset::className()]]);
// заменить на собранное
$this->registerCssFile('@frontendUrl/slick/slick-theme.css');
$this->registerCssFile('@frontendUrl/slick/slick.css');

$this->registerJs(
    <<<JS
    // $('.product-list.view-type-tile').each(function( index ){
    //     let maxheight = 0 ;
    //     $(this).find('.product-title a').each(function( index ){
    //         if ($(this).height() > maxheight) {
    //          maxheight = $(this).height(); 
    //         };
    //     });
    //     $(this).find('.product-title').css('height', maxheight);
    // });
    $('.rost-sidebar .catalog-menu__block .nav .dropdown').on('click', function() {
        $( this ).toggleClass('active');
    });
    $('.filter-widget .filter-item-title').on('click', function() {
        $( this ).parent().toggleClass('active');
        $( this ).parent().find('.filter-item-options').toggleClass('active');
    });
    $('.filter-widget').find('.filter-item-options, .filter-item').addClass('active');
    $('.banners-block .slider-inner').slick({
        infinite: true,
        slidesToShow: 2,
        slidesToScroll: 1,
        adaptiveHeight: true,
        // autoplay: true,
        dots:true,
        autoplaySpeed: 6000,
        arrows: false,
        responsive: [
    {
      breakpoint: 900,
      settings: {
         slidesToShow: 1
      }
    }
  ]
    });
JS
    , View::POS_READY);
$position = rand(1,9);
?>
<div class="catalog-page">
    <div class="rost-container">
        <div class="catalog-page__grid">
            <div class="rost-sidebar">
                <div class="catalog-menu__block">
                    <?php echo Nav::widget([
                        'options' => ['class' => ''],
                        'items' => frontend\components\CatalogMenuHelper::getMenu(),
                    ]); ?>
                </div>

                <?= FilterWidget::widget([
                    'model' => $category,
                    'totalCount' => $pages->totalCount,
                ]) ?>
                <div class="banner__block">
                    <?php echo Banner::widget([
                        'key' => 'sidebar',
                    ]); ?>
                </div>
            </div>
            <div class="catalog-page__content">
                <?php echo Breadcrumbs::widget([
                    'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
                ]) ?>
                <h1>
                    <?= Html::encode($category->title) ?>
                </h1>
                <div class="block-preferences clearfix">
                    <?= $this->render('_preferences', ['model' => $preferences]) ?>
                    <div class="catalog-mobile__filter-modal-widget">
                    <?php yii\bootstrap\Modal::begin([
                    'header' => '<h2>'.Yii::t('frontend','Filters').'</h2>',
                     'toggleButton' => [
                         'label' => false,
                         'tag' => 'div',
                         'class' => 'mobile-filter mobile-block',
                     ],
                        'options' =>['id'=>'mobile-filter-id','style' => 'top:55px;']
                    ]);
                        yii\bootstrap\Modal::end();?>
                    </div>

                </div>
                <div class="catalog-page__active-filter-widget">
                    <?= FilterActiveWidget::widget([
                        'model' => $category,
                    ]) ?>
                    <button class="non-standard-product" type="button"><?=Yii::t('frontend', 'Custom Product')?></button>
                </div>

                <div class="banners-block slider">
                    <?php echo DbCarousel::widget([
                        'key' => 'collection',
                        'assetManager' => Yii::$app->getAssetManager(),
                        'options' => [
                            'class' => 'collections', // enables slide effect
                        ],
                    ]) ?>
                </div>
                <div class="product-list clearfix view-type-<?= $preferences->viewType ?>">
                    <?php if (!empty($products)) : ?>
                        <?php if ($preferences->viewType == Preferences::VIEW_TYPE_LIST ) :?>
                             <table class="product-table-list">
                                 <thead>
                                 <tr>
                                     <th><?=Yii::t('frontend', 'The product name')?></th>
                                     <th><?=Yii::t('frontend', 'Availability')?></th>
                                     <th><?=Yii::t('frontend', 'Price')?></th>
                                     <th></th>
                                 </tr>
                                 </thead>
                                 <tbody>
                        <?php endif ?>
                        <?php foreach ($products as $key => $product) : ?>
                            <?php if ($key == $position) : ?>
                                <?php if ($preferences->viewType == Preferences::VIEW_TYPE_LIST) :?>
                                    <tr class="banner__block"><td colspan="4">
                                        <?php echo Banner::widget(['key' => 'product_'.$preferences->viewType]); ?>
                                    </td></tr>
                                <?php else: ?>
                                    <div class="banner__block">
                                    <?php echo Banner::widget(['key' => 'product_'.$preferences->viewType]); ?>
                                    </div>
                                <?php endif ?>
                            <?php endif; ?>
                            <?= $this->render('_product_'.$preferences->viewType, [
                                'model' => $product,
                            ]) ?>
                        <?php endforeach ?>
                        <?php if ($preferences->viewType == Preferences::VIEW_TYPE_LIST) :?>
                                     </tbody>
                                 </table>
                        <?php endif ?>
                    <?php else : ?>
                        <p><?= Yii::t('frontend','Products not found'); ?></p>
                    <?php endif ?>
                </div>

                <?php if($pages->totalCount): ?>
                <div class="catalog-page__progress-bar">
                    <?php $count_items = count($products) + $pages->page * $pages->pageSize ; ?>

                    <p class="heading"><?php printf(Yii::t('frontend','You have watched <span>%s</span> of <span class="total_count_prod">%s</span> products'), $count_items, $pages->totalCount) ?></p>
                    <div class="rost-progress-bar">
                        <div class="progress-bar-indicator" style="width:<?= ( $count_items/$pages->totalCount ) * 100?>%"></div>
                    </div>
                </div>
                <div class="load-more-block">
                    <a href="#" class="catalog-load-more"><?=Yii::t('frontend', 'Look more')?></a>
                </div>
                <?php endif; ?>
                <!-- <div class="content">
                     <?php /*= $category->content */ ?>
                 </div> -->
                <?= LinkPager::widget([
                    'pagination' => $pages,
                    'maxButtonCount' => 3,
                    'options' =>[
                        'hidden' => true,
                        'class' =>'pagination-ul',
                    ]
                ]); ?>
            </div>
        </div>

    </div>
</div>
