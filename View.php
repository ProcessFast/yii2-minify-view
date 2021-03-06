<?php
/**
 * View.php
 * @author Revin Roman
 * @link https://processfast.ru
 */

namespace processfast\yii\minify;

use yii\base\Event;
use yii\helpers\FileHelper;
use yii\web\AssetBundle;
use yii\web\Response;



/**
 * Class View
 * @package processfast\yii\minify
 */
class View extends \yii\web\View
{

    /**
     * @var bool
     */
    public $enableMinify = true;

    /**
     * @var string filemtime or sha1
     */
    public $fileCheckAlgorithm = 'sha1';

    /**
     * @var bool
     */
    public $concatCss = true;

    /**
     * @var bool
     */
    public $minifyCss = true;

    /**
     * @var bool
     */
    public $concatJs = true;

    /**
     * @var bool
     */
    public $minifyJs = true;

    /**
     * @var bool
     */
    public $minifyOutput = false;

    /**
     * @var bool
     */
    public $removeComments = true;

    /**
     * @deprecated
     * @var string path alias to web base (in url)
     */
    public $web_path = '@web';

    /**
     * @var string path alias to web base (in url)
     */
    public $webPath;

    /**
     * @deprecated
     * @var string path alias to web base (absolute)
     */
    public $base_path = '@webroot';

    /**
     * @var string path alias to web base (absolute)
     */
    public $basePath;

    /**
     * @deprecated
     * @var string path alias to save minify result
     */
    public $minify_path = '@webroot/minify';

    /**
     * @var string path alias to save minify result
     */
    public $minifyPath;

    /**
     * @deprecated
     * @var array positions of js files to be minified
     */
    public $js_position = [self::POS_END, self::POS_HEAD];

    /**
     * @var array positions of js files to be minified
     */
    public $jsPosition;

    /**
     * @var array options of minified js files
     */
    public $jsOptions = [];

    /**
     * @deprecated
     * @var bool|string charset forcibly assign, otherwise will use all of the files found charset
     */
    public $force_charset = false;

    /**
     * @var bool|string charset forcibly assign, otherwise will use all of the files found charset
     */
    public $forceCharset;

    /**
     * @deprecated
     * @var bool whether to change @import on content
     */
    public $expand_imports = true;

    /**
     * @var bool whether to change @import on content
     */
    public $expandImports;

    /**
     * @deprecated
     * @var int
     */
    public $css_linebreak_pos = 2048;

    /**
     * @var int
     */
    public $cssLinebreakPos;

    /**
     * @deprecated
     * @var int|bool chmod of minified file. If false chmod not set
     */
    public $file_mode = 0664;

    /**
     * @var int|bool chmod of minified file. If false chmod not set
     */
    public $fileMode;

    /**
     * @var array schemes that will be ignored during normalization url
     */
    public $schemas = ['//', 'http://', 'https://', 'ftp://'];

    /**
     * @deprecated
     * @var bool do I need to compress the result html page.
     */
    public $compress_output = false;

    /**
     * @deprecated
     * @var array options for compressing output result
     *   * extra - use more compact algorithm
     *   * no-comments - cut all the html comments
     */
    public $compress_options = ['extra' => true];

    /**
     * @var array options for compressing output result
     *   * extra - use more compact algorithm
     *   * no-comments - cut all the html comments
     */
    public $compressOptions;

    /**
     * @var array
     */
    public $excludeBundles = [];

    /**
     * @var array
     */
    public $excludeFiles = [];

    /**
     * @var boolean
     */
    public $S3Upload = false ;


    /**
     * @var boolean
     */
    public $awsBucket = null ;

    /**
     * @var boolean
     * It is for linking Resource folder to asset files
     * if Resources like images above one folder it should be "../" if two folders above "../../"
     */
    public $assetsFolderPathPatch = null ;


    /*
     * boolean
     * backend checke will help keep assets into root/minify folder instead of root/backend/minifiy for backend
     */
    public $backendCheck = false ;
    /*
     * Folder name where minified files will be kept
     */
    public $folderName = 'minify' ;
    /*
     * will be used at _getSummaryFilesHash will fix path to have same hash value as frontend or backend when files generated from console.
     */
    public $modifyPath = false  ;
    public $modifyPathData = "" ;

    /**
     * This one will be added as JS file prefix while it will be uploaded to S3 bucket
     * @var string
     */
    public $prefixJsFile = "" ;

    /**
     * This one will be added as CSS file prefix while it will be uploaded to S3 bucket
     * @var string
     */
    public $prefixCssFile = "" ;

    /**
     * by the param it will be decided whether to encode content of js files into gzip or not
     * @var bool
     */
    public $gzipEncodeJs = false ;

    /**
     * by the param it will be decided whether to encode content of css files into gzip or not
     * @var bool
     */
    public $gzipEncodeCss = false ;


    /**
     * this will tell the versionNumber of app. It will be included in filename while it will be uploaded to S3 bucket
     * @var string
     */
    public $versionNumber = "";

    /**
     * @throws \processfast\yii\minify\Exception
     */
    public function init()
    {
        parent::init();

        $this->webPath = empty($this->webPath) ? $this->web_path : $this->webPath;
        $this->basePath = empty($this->basePath) ? $this->base_path : $this->basePath;
        $this->minifyPath = empty($this->minifyPath) ? $this->minify_path : $this->minifyPath;
        $this->jsPosition = empty($this->jsPosition) ? $this->js_position : $this->jsPosition;
        $this->forceCharset = empty($this->forceCharset) ? $this->force_charset : $this->forceCharset;
        $this->expandImports = empty($this->expandImports) ? $this->expand_imports : $this->expandImports;
        $this->cssLinebreakPos = empty($this->cssLinebreakPos) ? $this->css_linebreak_pos : $this->cssLinebreakPos;
        $this->fileMode = empty($this->fileMode) ? $this->file_mode : $this->fileMode;
        $this->compressOptions = empty($this->compressOptions) ? $this->compress_options : $this->compressOptions;

        if( $this->backendCheck )
        {
            $appId = \Yii::$app->id ;
            if( $appId == "app-frontend" )
            {
                $this->minifyPath = $this->minifyPath."/".$this->folderName;
            }
            else if( $appId == "app-backend" )
            {
                $this->minifyPath = $this->minifyPath."/../".$this->folderName;
            }
        }

        $excludeBundles = $this->excludeBundles;
        if (!empty($excludeBundles)) {
            foreach ($excludeBundles as $bundle) {
                if (!class_exists($bundle)) {
                    continue;
                }

                /** @var AssetBundle $Bundle */
                $Bundle = new $bundle;

                if (!empty($Bundle->css)) {
                    $this->excludeFiles = array_merge($this->excludeFiles, $Bundle->css);
                }

                if (!empty($Bundle->js)) {
                    $this->excludeFiles = array_merge($this->excludeFiles, $Bundle->js);
                }
            }
        }

        $minify_path = $this->minifyPath = (string)\Yii::getAlias($this->minifyPath);
        if (!file_exists($minify_path)) {
            FileHelper::createDirectory($minify_path);
        }

        if (!is_readable($minify_path)) {
            throw new Exception('Directory for compressed assets is not readable.');
        }

        if (!is_writable($minify_path)) {
            throw new Exception('Directory for compressed assets is not writable.');
        }

        if (true === $this->enableMinify && (true === $this->minifyOutput || true === $this->compress_output)) {
            \Yii::$app->response->on(Response::EVENT_BEFORE_SEND, function (Event $Event) {
                /** @var Response $Response */
                $Response = $Event->sender;

                if ($Response->format === Response::FORMAT_HTML) {
                    if (!empty($Response->data)) {
                        $Response->data = HtmlCompressor::compress($Response->data, $this->compressOptions);
                    }

                    if (!empty($Response->content)) {
                        $Response->content = HtmlCompressor::compress($Response->content, $this->compressOptions);
                    }
                }
            });
        }
    }

    /**
     * @inheritdoc
     */
    public function endBody()
    {
        $this->trigger(self::EVENT_END_BODY);
        echo self::PH_BODY_END;

        foreach (array_keys($this->assetBundles) as $bundle) {
            $this->registerAssetFiles($bundle);
        }

        if (true === $this->enableMinify) {
            (new components\CSS($this))->export();
            (new components\JS($this))->export();
        }
    }
}
