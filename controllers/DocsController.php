<?php

namespace app\controllers;

use app\models\DocsChapter;
use app\models\DocsSection;
use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;

/**
 * DocsController - публичный контроллер документации
 */
class DocsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public $layout = '@app/views/docs/layouts/docs';

    /**
     * Главная страница документации (оглавление)
     *
     * @return string
     */
    public function actionIndex()
    {
        $chapters = DocsChapter::getActiveChaptersWithSections();

        return $this->render('index', [
            'chapters' => $chapters,
        ]);
    }

    /**
     * Страница главы
     *
     * @param string $slug
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionChapter($slug)
    {
        $chapter = DocsChapter::findBySlug($slug);

        if (!$chapter) {
            throw new NotFoundHttpException('Глава не найдена');
        }

        // Если есть секции, редиректим на первую
        $firstSection = $chapter->getFirstSection();
        if ($firstSection) {
            return $this->redirect(['docs/section', 'chapter' => $chapter->slug, 'slug' => $firstSection->slug]);
        }

        $chapters = DocsChapter::getActiveChaptersWithSections();

        return $this->render('chapter', [
            'chapter' => $chapter,
            'chapters' => $chapters,
        ]);
    }

    /**
     * Страница секции
     *
     * @param string $chapter
     * @param string $slug
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionSection($chapter, $slug)
    {
        $chapterModel = DocsChapter::findBySlug($chapter);

        if (!$chapterModel) {
            throw new NotFoundHttpException('Глава не найдена');
        }

        $section = DocsSection::findBySlug($slug, $chapterModel->id);

        if (!$section) {
            throw new NotFoundHttpException('Раздел не найден');
        }

        $chapters = DocsChapter::getActiveChaptersWithSections();
        $prevNext = $section->getPrevNextSections();
        $headings = $section->getHeadings();

        return $this->render('section', [
            'chapter' => $chapterModel,
            'section' => $section,
            'chapters' => $chapters,
            'prevSection' => $prevNext['prev'],
            'nextSection' => $prevNext['next'],
            'headings' => $headings,
        ]);
    }

    /**
     * Поиск по документации
     *
     * @return string
     */
    public function actionSearch()
    {
        $query = Yii::$app->request->get('q', '');
        $results = DocsSection::search($query);
        $chapters = DocsChapter::getActiveChaptersWithSections();

        return $this->render('search', [
            'query' => $query,
            'results' => $results,
            'chapters' => $chapters,
        ]);
    }
}
