<?php

namespace frontend\modules\shop\controllers;

use common\models\Category;
use common\models\FavorireProduct;
use common\models\Preferences;
use common\models\Product;
use yii\data\Pagination;
use yii\web\Response;
use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Cookie;

class ProductController extends Controller
{
    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionList($id)
    {
        /** @var Category $category */
        $category = Category::find()->localized()->where(['id' => $id])->one();
        if (null === $category) {
            throw new NotFoundHttpException();
        }
        $get = ArrayHelper::merge(Yii::$app->request->get(), Yii::$app->request->post());
        $properties = isset($get['property']) ? $get['property'] : [];

        $products = Product::getFilteredProducts($category->id, $properties);

        $this->view->title = $category->seo_title;
        $this->view->registerMetaTag([
            'name' => 'description',
            'content' => $category->seo_description,
        ]);

        return $this->render('list', [
            'category' => $category,
            'products' => $products['products'],
            'pages' => $products['pages'],
            'preferences' => Preferences::preferences(),
            'breadcrumbs' => $this->buildBreadcrumbsArray($category),
        ]);
    }

    /**
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionShow($id)
    {
        /** @var Product $product */
        $product = Product::find()->localized()->where(['id' => $id])->one();
        if (null === $product) {
            throw new NotFoundHttpException();
        }

        $this->view->title = $product->seo_title;
        $this->view->registerMetaTag([
            'name' => 'description',
            'content' => $product->seo_description,
        ]);
        $allviewed = $this->setViwedProduct($id);
        /** @var Category $category */
        $category = Category::find()->localized()->where(['id' => $product->main_category_id])->one();

        return $this->render('show', [
            'product' => $product,
            'breadcrumbs' => $this->buildBreadcrumbsArray($category, $product)
        ]);
    }

    /**
     * This function build array for widget "Breadcrumbs"
     * @param Category $category - model of current category
     * @param Product|null $product - model of product, if current page is a page of product
     * Return an array for widget or empty array
     * @return array
     */
    private function buildBreadcrumbsArray($category, $product = null)
    {
        if ($category === null) {
            return [];
        }
        // init
        $breadcrumbs = [];
        if ($product !== null) {
            $crumbs[$product->slug] = !empty($product->title) ? $product->title : '';
        }
        $crumbs[$category->slug] = $category->title;

        // get basic data
        $parent = $category->parent_id > 0 ? Category::find()->localized()->where(['id' => $category->parent_id])->one() : null;
        while ($parent !== null) {
            $crumbs[$parent->slug] = $parent->title;
            $parent = $parent->parent;
        }

        // build array for widget
        $url = '';
        $positionCounter = 2;
        $crumbs = array_reverse($crumbs, true);
        foreach ($crumbs as $slug => $label) {
            $url .= '/' . $slug;
            $breadcrumbs[] = [
                'label' => $label,
                'url' => $url,
            ];
            $positionCounter++;
        }
        unset($breadcrumbs[count($breadcrumbs) - 1]['url']); // last item is not a link

        return $breadcrumbs;
    }


    /**
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */    public function actionViewedList()
    {
        $viewed_products = Yii ::$app -> request -> cookies -> get('viewed_products');
        $viewed_products = json_decode($viewed_products);

        $products = Product::getFavoriteFilteredProducts($viewed_products);

        return $this->render('favorite_list', [
            'products' => $products['products'],
            'pages' => $products['pages'],
            'preferences' => Preferences::preferences(),
            'breadcrumbs' => [ 'label' => Yii::t('frontend','Viewed'). ' ' . Yii::t('frontend','products') ]
        ]);

    }
}
