<?php


use common\models\Article;
use common\models\ArticleAttachment;
use common\models\ArticleCategory;
use common\models\ArticleCategoryLanguage;
use common\models\ArticlesLanguage;
use yii\db\Migration;

class m140703_123803_article extends Migration
{
    /**
     * @return bool|void
     */
    public function safeUp()
    {
        $this->createTable(ArticleCategory::tableName(), [
            'id' => $this->primaryKey(),
            'slug' => $this->string(255),
            'parent_id' => $this->integer(),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createTable(Article::tableName(), [
            'id' => $this->primaryKey(),
            'slug' => $this->string(255),
            'view' => $this->string(),
            'category_id' => $this->integer(),
            'parent_id' => $this->integer(),
            'thumbnail_base_url' => $this->string(1024),
            'thumbnail_path' => $this->string(1024),
            'status' => $this->smallInteger()->notNull()->defaultValue(0),
            'created_by' => $this->integer(),
            'updated_by' => $this->integer(),
            'published_at' => $this->integer(),
            'created_at' => $this->integer(),
            'updated_at' => $this->integer(),
        ]);

        $this->createTable(ArticleAttachment::tableName(), [
            'id' => $this->primaryKey(),
            'article_id' => $this->integer()->notNull(),
            'path' => $this->string()->notNull(),
            'base_url' => $this->string(),
            'type' => $this->string(),
            'size' => $this->integer(),
            'name' => $this->string(),
            'created_at' => $this->integer()
        ]);

        $this->createTable(ArticlesLanguage::tableName(), [
            'id' => $this->primaryKey(),
            'article_id' => $this->integer(),
            'language'=>$this->string(16)->notNull(),
            'title'=>$this->string(512)->notNull(),
            'body'=>$this->text(),
            'short_description'=>$this->string(512)->notNull(),
            'seo_title'=>$this->string(255)->notNull(),
            'seo_description'=>$this->string(512)->notNull(),
        ]);

        $this->createTable(ArticleCategoryLanguage::tableName(), [
            'id' => $this->primaryKey(),
            'article_category_id' => $this->integer(),
            'language'=>$this->string(16)->notNull(),
            'title' => $this->string(512)->notNull(),
            'body' => $this->text(),
            'seo_title'=>$this->string(255)->notNull(),
            'seo_description'=>$this->string(512)->notNull(),
        ]);

        $this->addForeignKey('fk_article_attachment_article', ArticleAttachment::tableName(), 'article_id', Article::tableName(), 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_article_author', Article::tableName(), 'created_by', '{{%user}}', 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_article_updater', Article::tableName(), 'updated_by', '{{%user}}', 'id', 'set null', 'cascade');
        $this->addForeignKey('fk_article_category', Article::tableName(), 'category_id', ArticleCategory::tableName(), 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_article_category_section', ArticleCategory::tableName(), 'parent_id', ArticleCategory::tableName(), 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_article_parent', Article::tableName(), 'parent_id', Article::tableName(), 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_articles_language', ArticlesLanguage::tableName(), 'article_id', Article::tableName(), 'id', 'cascade', 'cascade');
        $this->addForeignKey('fk_article_category_language', ArticleCategoryLanguage::tableName(), 'article_category_id', ArticleCategory::tableName(), 'id', 'cascade', 'cascade');

        $this->createIndex(  'idx-articles_language-lang',ArticlesLanguage::tableName(),'language');
        $this->createIndex(  'idx-article_category_language-lang',ArticleCategoryLanguage::tableName(),'language');
        $this->createIndex('idx_article_slug', Article::tableName(), 'slug', true);
        $this->createIndex('idx_article_category_slug', ArticleCategory::tableName(), 'slug', true);
    }

    /**
     * @return bool|void
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk_article_attachment_article', ArticleAttachment::tableName());
        $this->dropForeignKey('fk_article_author', Article::tableName());
        $this->dropForeignKey('fk_article_updater', Article::tableName());
        $this->dropForeignKey('fk_article_category', Article::tableName());
        $this->dropForeignKey('fk_article_category_section', ArticleCategory::tableName());
        $this->dropForeignKey('fk_article_parent',Article::tableName());
        $this->dropForeignKey('fk_articles_language',ArticlesLanguage::tableName());
        $this->dropForeignKey('fk_article_category_language',ArticlesLanguage::tableName());

        $this->dropIndex('idx-articles_language-lang',ArticlesLanguage::tableName());
        $this->dropIndex('idx-article_category_language-lang',ArticleCategoryLanguage::tableName());
        $this->dropIndex('idx_article_slug', Article::tableName());
        $this->dropIndex('idx_article_category_slug', ArticleCategory::tableName());

        $this->dropTable(ArticleAttachment::tableName());
        $this->dropTable(Article::tableName());
        $this->dropTable(ArticleCategory::tableName());
        $this->dropTable(ArticlesLanguage::tableName());
        $this->dropTable(ArticleCategoryLanguage::tableName());
    }
}
