<?php

namespace app\modules\admin\controllers;

use app\models\Category;
use app\models\ImageUpload;
use Yii;
use app\models\Article;
use app\modules\ArticleSearch;
use yii\helpers\ArrayHelper;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\UploadedFile;

/**
 * ArticleController implements the CRUD actions for Article model.
 */
class ArticleController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all Article models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new ArticleSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single Article model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new Article model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        /*создается новый экзепляр статьи как экзепляр класса*/
        $model = new Article();

        /*так можно посмотреть какие запросы и куда уходят*/
//        if( $_POST['Article'])
//        {
//            var_dump($_POST['Article']);
//            var_dump(Yii::$app->request->post());die;
//            $model->title = $_POST['Article']['title'];
//            var_dump($model->title);die;
//            $model->load(Yii::$app->request->post());
//            var_dump($model->attributes);die;
//        }

        /*если статья заполнена и ма нажали кнопку create, то перевести на страницу созданной статьи*/
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        /*отобразить созданную статью*/
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing Article model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing Article model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * Finds the Article model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Article the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Article::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException('The requested page does not exist.');
    }

    /*Action для загрузки картинки
    в этот action приводит ссылка из кнопки*/
    public function actionSetImage($id)
    {
        /*создается экземпляр модели по загрузке картинок*/
        $model = new ImageUpload;
        //метод загрузки картинки POST
        if (Yii::$app->request->isPost)
        {
            //метод запрома в бд для вызова картинки по id статьи
            $article = $this->findModel($id);
            //получаем загруженный файл
            $file = UploadedFile::getInstance($model, 'image');
            /*вернуть имя текущей картинки после загрузки
            и передаем имя картинки в imageUpload.php, для проверки названия и прочих параметров,
            а затем отправит в базу данных Article.php*/
            if($article->saveImage($model->uploadFile($file, $article->image)))
            {
                /*если картинка успешно загружена, то перенаправляем пользователя в статью,
                которую редактировали*/
                return $this->redirect(['view', 'id'=>$article->id]);
            }
        }

        /*выводится вид для формы*/
        return $this->render('image', ['model'=>$model]); //рендер для вывода картинки
    }

    public function actionSetCategory($id)
    {
        $article = $this->findModel($id);
        $selectedCategory = $article->category->id;
        $categories = ArrayHelper::map(Category::find()->all(), 'id', 'title');
        if(Yii::$app->request->isPost)
        {
            $category = Yii::$app->request->post('category');
            if($article->saveCategory($category))
            {
                return $this->redirect(['view', 'id'=>$article->id]);
            }
        }
        return $this->render('category', [
            'article'=>$article,
            'selectedCategory'=>$selectedCategory,
            'categories'=>$categories
        ]);
    }
}
