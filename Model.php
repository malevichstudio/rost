<?php

namespace common\models;

use common\behaviors\CustomUploadBehavior;
use common\models\query\CategoryQuery;
use omgdef\multilingual\MultilingualBehavior;
use trntv\filekit\behaviors\UploadBehavior;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\SluggableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "category".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $slug
 * @property string $title
 * @property string $seo_title
 * @property string $seo_description
 * @property string $content
 * @property string $announce
 * @property string $view
 * @property integer $order
 * @property integer $is_active
 * @property integer $created_at
 * @property integer $updated_at
 * @property int $created_by
 * @property int $updated_by
 * @property string $fullSlug
 *
 * Relations:
 * @property Category[] $children
 * @property Category $parent
 * @property Product[] $products
 * @property Filter[] $filters
 */
class Category extends ActiveRecord
{
    const STATUS_DRAFT = 0;
    const STATUS_PUBLISHED = 1;

    /**
     * @var array
     */
    public $thumbnail;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%category}}';
    }

    /**
     * @return CategoryQuery
     */
    public static function find()
    {
        return new CategoryQuery(get_called_class());
    }

    /**
     * @return array statuses list
     */
    public static function statuses()
    {
        return [
            self::STATUS_DRAFT => Yii::t('common', 'Draft'),
            self::STATUS_PUBLISHED => Yii::t('common', 'Published'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
            BlameableBehavior::class,
            'slug' => [
                'class' => SluggableBehavior::class,
                'attribute' => 'title',
                'ensureUnique' => true,
                'immutable' => true
            ],
            'ml' => [
                'class' => MultilingualBehavior::class,
                'languages' => Yii::$app->params['availableLocales'],
                'langClassName' => CategoryLang::class,
                'defaultLanguage' => Yii::$app->language,
                'langForeignKey' => 'category_id',
                'tableName' => CategoryLang::tableName(),
                'requireTranslations' => true,
                'attributes' => [
                    'title', 'content', 'announce', 'seo_title', 'seo_description'
                ]
            ],
            [
                'class' => CustomUploadBehavior::class,
                'attribute' => 'thumbnail',
                'pathAttribute' => 'thumbnail_path',
                'baseUrlAttribute' => 'thumbnail_base_url',
                'altAttribute' => 'thumbnail_alt',
                'titleAttribute' => 'thumbnail_title',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'seo_title', 'code'], 'required'],
            ['parent_id', 'default', 'value' => 0],
            ['view', 'default', 'value' => 'view'],
            [['parent_id', 'order'], 'integer'],
            [['content', 'announce'], 'string'],
            ['is_active', 'integer'],
            ['is_active', 'in', 'range' => array_keys(self::statuses())],
            [['slug', 'code'], 'unique'],
            ['seo_description', 'string', 'max' => 512],
            [['view', 'seo_title', 'slug', 'title'], 'string', 'max' => 255],
            [['thumbnail_base_url', 'thumbnail_path'], 'string', 'max' => 1024],
            ['code', 'string', 'max' => 32],
            [['thumbnail'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('common', 'ID'),
            'slug' => Yii::t('common', 'Slug'),
            'title' => Yii::t('common', 'Title'),
            'code' => Yii::t('common', 'Import code'),
            'seo_title' => Yii::t('common', 'Seo Title'),
            'seo_description' => Yii::t('common', 'Seo Description'),
            'content' => Yii::t('common', 'Content'),
            'announce' => Yii::t('common', 'Announce'),
            'view' => Yii::t('common', 'Page View'),
            'is_active' => Yii::t('common', 'Active'),
            'created_at' => Yii::t('common', 'Created At'),
            'updated_at' => Yii::t('common', 'Updated At'),
            'parent_id' => Yii::t('common', 'Parent Category'),
            'order' => Yii::t('common', 'Sort order'),
            'created_by' => Yii::t('common', 'Author'),
            'updated_by' => Yii::t('common', 'Updater'),
            'thumbnail' => Yii::t('common', 'Thumbnail'),
        ];
    }

    public function beforeSave($insert)
    {
        if ($insert) {
            $this->order = self::find()->max('id') + 1;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return \yii\db\ActiveQuery|User
     */
    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery|User
     */
    public function getUpdater()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    /**
     * @return Category|null
     */
    public function getParent()
    {
        return self::findOne(['id' => $this->parent_id]);
    }

    /**
     * @return Category[]
     */
    public function getChildren()
    {
        return self::findAll(['parent_id' => $this->id]);
    }

    /**
     * @return \yii\db\ActiveQuery|Product[]
     */
    public function getProducts()
    {
        return $this->hasMany(Product::class, ['id' => 'product_id'])
            ->viaTable(ProductCategory::tableName(), ['category_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery|Filter[]
     */
    public function getFilters()
    {
        return $this->hasMany(Filter::class, ['category_id' => 'id'])->orderBy('sort_order');
    }

    /**
     * @return string
     */
    public function getFullSlug()
    {
        $cacheKey = "Category:fullSlug:" . $this->id;
        $result = Yii::$app->cache->get($cacheKey);
        if ($result !== false) {
            return $result;
        }
        $result = $this->slug;
        if ($this->parent_id !== 0) {
            $result = $this->parent->fullSlug . '/' . $result;
        }
        Yii::$app->cache->set($cacheKey, $result, 86400);
        return $result;
    }

    /**
     * @param $ids array
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function saveFilters($ids)
    {
        foreach ($this->filters as $filter) {
            $filter->delete();
        }
        foreach ($ids as $key => $propertyId) {
            Yii::$app->db->createCommand()->insert(
                Filter::tableName(),
                [
                    'category_id' => $this->id,
                    'property_id' => $propertyId,
                    'sort_order' => $key,
                ]
            )->execute();
        }
    }
}
