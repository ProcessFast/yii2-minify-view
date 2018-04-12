Yii 2 Minify View Component
===========================

Purpose of the Yii2 extension
-----------------------------
The main feature of this component is to concatenate and compress [GZIP] JS and CSS files listed in "AssetBundle" and allow application an option to upload them to AWS S3 bucket directly from minify folder on file generation.And present copressed files from S3 bucket to browser. Concatenation and compression of all files will be done on first time loading after deployment, for all the next requests assets will only be requested from S3 bucket.Optionally console request can also be sent to have all the assets coming from S3 bucket directly. Console request must be part of continuous deployment script.  


Required to work accurately
-------------------------------
* It works with layouts. For instance different layouts can have different JS & CSS files which can be listed in different AssetBundle. 
* There should be only one main AssetBundle which has all other asset bundles as dependecies. [Please check here how I have managed my assets.](https://github.com/ProcessFast/yii2-minify-view#my-asset-bundles-dependencies) 
* All JS & CSS files [ mostly JS pluggins ] used in particular layout whether it belongs to a particular page which only required on widget initialization must be also added at in the main Asset file under dependency.  


[![License](https://poser.pugx.org/ProcessFast/yii2-minify-view/license.svg)](https://packagist.org/packages/ProcessFast/yii2-minify-view)
[![Latest Stable Version](https://poser.pugx.org/ProcessFast/yii2-minify-view/v/stable.svg)](https://packagist.org/packages/ProcessFast/yii2-minify-view)
[![Latest Unstable Version](https://poser.pugx.org/ProcessFast/yii2-minify-view/v/unstable.svg)](https://packagist.org/packages/ProcessFast/yii2-minify-view)
[![Total Downloads](https://poser.pugx.org/ProcessFast/yii2-minify-view/downloads.svg)](https://packagist.org/packages/ProcessFast/yii2-minify-view)

Code Status
-----------
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ProcessFast/yii2-minify-view/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ProcessFast/yii2-minify-view/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/ProcessFast/yii2-minify-view/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ProcessFast/yii2-minify-view/?branch=master)
[![Travis CI Build Status](https://travis-ci.org/ProcessFast/yii2-minify-view.svg)](https://travis-ci.org/ProcessFast/yii2-minify-view)
[![Dependency Status](https://www.versioneye.com/user/projects/54119b4b9e1622a6510000e1/badge.svg)](https://www.versioneye.com/user/projects/54119b4b9e1622a6510000e1)

Support
-------
[GutHub issues](https://github.com/ProcessFast/yii2-minify-view/issues).

Installation
------------

The preferred way to install this extension is through [composer](https://getcomposer.org/).

Either run

```bash
composer require "processfast/yii2-minify-view:~1.14"
```

or add

```
"processfast/yii2-minify-view": "~1.14",
```

to the `require` section of your `composer.json` file.

Configure
---------
```php
<?php

return [
	// ...
	'components' => [
		// ...
		'view' => [         
			'class' => '\processfast\yii\minify\View',
			'enableMinify' => !YII_DEBUG,
			'concatCss' => true, // concatenate css
			'minifyCss' => true, // minificate css
			'concatJs' => true, // concatenate js
			'minifyJs' => true, // minificate js
			'minifyOutput' => true, // minificate result html page
			'webPath' => '@web', // path alias to web base
			'basePath' => '@webroot', // path alias to web base
			'minifyPath' => '@webroot/minify', // path alias to save minify result
			'jsPosition' => [ \yii\web\View::POS_END ], // positions of js files to be minified
			'forceCharset' => 'UTF-8', // charset forcibly assign, otherwise will use all of the files found charset
			'expandImports' => true, // whether to change @import on content
			'compressOptions' => ['extra' => true], // options for compress
			'excludeFiles' => [
            	'jquery.js', // exclude this file from minification
            	'app-[^.].js', // you may use regexp
            ],
            'excludeBundles' => [
            	\dev\helloworld\AssetBundle::class, // exclude this bundle from minification
            ],
            
            // Extra options added in this extention fork
   
            'S3Upload'=> true,         
            'awsBucket'=> null,
            'assetsFolderPathPatch'=>null,
   
            'backendCheck'=>false,
            'folderName'=>'minify',

            // This 2 options only used when you are generating from Script before deployment from Console Controller
            'modifyPath'=>false,
            'modifyPathData'=>"",


            'layoutPrefixArray'=>[],
            'layoutPrefixCss'=>false,
            'layoutPrefixJS'=>false
            
            
		]
	]
];
```

Documentation/Explanation of the Configuration Options
------------------------------------------------------
### New Configuration Options

```
 /**
     * @var boolean
     * whether you want to use S3Bucket or not
     * By default it will be false
     */
    public $S3Upload = false ;

    /**
     * @var boolean
     * Name of awsBucket
     */
    public $awsBucket = null ;

    /**
     * @var boolean
     * It is for linking Resource folder to asset files
     * if Resources like images above one folder it should be "../" if two folders above "../../"
     * Assume scenario where application wants to load all the images from s3 now you have images folder in root and you have dev, qa, prod folder which have JS/CSS files in that case this option is helpful to make a connection between assets and resources.
     */
    public $assetsFolderPathPatch = null ;

    /*
     * @var boolean
     * If application is advanced Yii2 application where assets for backend and frontend generated in different assets folder.
     * For instance in nenand/yii2-advanced-templated assets for frontend are generated in ROOT/assets folder and for backend assets are generated in ROOT/backend/assets
     * This option is for minify folder so if all the assets in backend are same as frontend, this option will allow application to just use ROOT/minify as the main folder by making it true. 
     */
    public $backendCheck = false ;

    /*
     * Folder name where minified files will be kept
     * It will only be used when $backendCheck is true
     * It will be helpful when backend and frontend both's all assets are same meaning all JS files and CSS files on backend and frontend are same. 
     */
    public $folderName = 'minify' ;

    /*
     * These two options are for console request. If application have functionality to generate concated and compressed assets at console request.
     *
     * In this extention last file name of generated file totally depends on location from where request has made. So web requests and console request will genrate same files but names will be different. To solve that and have uniform name across WEB and console this two options are used. 
     * first option is boolean which is to determine whether application wants to use this feature or not.
     * Second option is string adjustment of path. Which should be removed from the file path to have same file names on console/web.
     * 
     * will be used at _getSummaryFilesHash will fix path to have same hash value as frontend or backend when files generated from console.
     * At console level this will be used as when generating from console path will be different so some adjustment path should be decalared t make path same as
     * of running in web browser as console has path from console folder script
     *
     * so when you generate from console  make modifyPath true
     * and modifyPathData regarding your assets folder to console folder
     */
    public $modifyPath = false  ;
    public $modifyPathData = "" ;

    /*
     * It helps if you want to add prefix to any file as it will mostly create file name
     * as {prefix}-all-in-one-{HASH}.{js/css}
     * so if you want to give a prefix for certain layout
     * then you can do it by this option.
     * Pass layout name as array key and pass prefix name as array value
     * ex :  for main layout if you want newmain prefix
     * you have to pass array like ["main"=>"newmain"]
     * if you do not wont prefix do not do anything just live it a blank array
     */
    public $layoutPrefixArray = [] ;

    /*
     * Use layoutPrefixArray option for css true/false
     */
    public $layoutPrefixCss = false ;

    /*
     * Use layoutPrefixArray option for Js true/false
     */
    public $layoutPrefixJS = false ;
```

## Dependecnies
* "yiisoft/yii2": "2.0.*",
* "mrclay/minify": "~2.2",
* "fedemotta/yii2-aws-sdk": "2.*"


## Yii2 configuration or structure that is expected for this extension to work

### Example of Asset bundles dependencies ( Required )

By giving dependencies and loading all JS/CSS files throught web application in a uniform sequence will create uniform minified JS/CSS files from extention for all pages as all pages have same JS/CSS files with same sequence. As the final name of JS/CSS file depends on the file content and file path so it is important to have configuration in this way.

**This is just example of my "bundles-minify.php" which handles dependency you can create yours on the basis of your asset bundles.**

```
<?php

//bundles-minify.php

if( YII_DEBUG )
{
    $jquery = "https://code.jquery.com/jquery-2.2.4.js" ;
}
else
{
    $jquery = "https://code.jquery.com/jquery-2.2.4.min.js" ;
}

return [

    'yii\web\JqueryAsset' => [
        'js'=>[$jquery]
    ],
    'common\assets\HighchartsAsset' => [
        'depends'=>[
            'yii\web\JqueryAsset'
        ],
    ],
    'yii\web\YiiAsset' => [
        'depends'=>[
            'common\assets\HighchartsAsset'
        ],
    ],
    'yii\validators\ValidationAsset' => [
        'depends'=>[
            'yii\web\YiiAsset'
        ],
    ],
    'yii\widgets\ActiveFormAsset' => [
        'depends'=>[
            'yii\validators\ValidationAsset'
        ],
    ],
    'yii\bootstrap\BootstrapAsset' => [
        'css' => [], // do not use yii default one,
        'depends'=>[
            'yii\widgets\ActiveFormAsset'
        ],
    ],
    'yii\widgets\MaskedInputAsset' => [
        'depends'=>[
            'yii\bootstrap\BootstrapAsset'
        ],
    ],
    'yii\jui\JuiAsset' => [
        'depends'=>[
            'yii\widgets\MaskedInputAsset'
        ],
    ],
    'common\assets\MomentJsAsset' => [
        'depends'=>[
            'yii\jui\JuiAsset'
        ],
    ],
    'common\assets\CDNAsset' => [
        'depends'=>[
            'common\assets\MomentJsAsset'
        ],
    ],
    'mihaildev\ckeditor\Assets' => [
        'depends'=>[
            'common\assets\CDNAsset'
        ],
    ],
    'yii\bootstrap\BootstrapPluginAsset' => [
        'depends'=>[
            'mihaildev\ckeditor\Assets'
        ],
    ],
    'kartik\form\ActiveFormAsset' => [
        'depends'=>[
            'yii\bootstrap\BootstrapPluginAsset'
        ],
    ],
    'kartik\time\TimePickerAsset' => [
        'depends'=>[
            'kartik\form\ActiveFormAsset'
        ],
    ],

    'kartik\file\SortableAsset' => [
        'depends'=>[
            'kartik\time\TimePickerAsset'
        ],
    ],
    'kartik\file\DomPurifyAsset' => [
        'depends'=>[
            'kartik\file\SortableAsset'
        ],
    ],

    'kartik\file\FileInputAsset' => [
        'depends'=>[
            'kartik\file\DomPurifyAsset'
        ],
    ],
    'kartik\dropdown\DropdownXAsset' => [
        'depends'=>[
            'kartik\file\FileInputAsset'
        ],
    ],
    'kartik\base\WidgetAsset' => [
        'depends'=>[
            'kartik\dropdown\DropdownXAsset'
        ],
    ],
    'common\assets\FontAwesomeAsset' => [
        'depends'=>[
            'kartik\base\WidgetAsset'
        ],
    ],
    'common\assets\IonIconsAsset' => [
        'depends'=>[
            'common\assets\FontAwesomeAsset'
        ],
    ],
    'common\assets\JqueryCreditCardValidatorAsset' => [
        'depends'=>[
            'common\assets\IonIconsAsset'
        ],
    ],
    'common\assets\ListJsAsset' => [
        'depends'=>[
            'common\assets\JqueryCreditCardValidatorAsset'
        ],
    ],
    'common\assets\MustacheJsAsset' => [
        'depends'=>[
            'common\assets\ListJsAsset'
        ],
    ],
    'common\assets\JsCookieAsset' => [
        'depends'=>[
            'common\assets\MustacheJsAsset'
        ],
    ],
    'common\assets\BootstrapDaterangePickerAsset' => [
        'depends'=>[
            'common\assets\JsCookieAsset'
        ],
    ],
    'common\assets\BootstrapDateTimePickerAsset' => [
        'depends'=>[
            'common\assets\BootstrapDaterangePickerAsset'
        ],
    ],
    'common\assets\BootstrapSwitchAsset' => [
        'depends'=>[
            'common\assets\BootstrapDateTimePickerAsset'
        ],
    ],
    'common\assets\AdminLTEAsset' => [
        'depends'=>[
            'common\assets\BootstrapSwitchAsset'
        ],
    ],
    'common\assets\AppAssetVersion2' => [
        'depends'=>[
            'common\assets\AdminLTEAsset'
        ],
    ],
    'common\assets\AppAsset' => [
        'depends'=>[
            'common\assets\AppAssetVersion2'
        ],
    ],
] ; 

```

### This is some fix I have to done at my main.php ( Required )

* Not allowing JS/CSS files to be load at AJAX requests as all of the needed will be there of the first request or main request.
```
        // start of config/main.php
        // removing js and css files being loaded on AJAX call
        $bundles = "bundles-minify.php"; // having dependencies
        $bundlesFiles = require_once( $bundles ) ;
        if(isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        {
            $bundlesFiles = false ;
        }
```


* Another thing application needed folder names generated from backend/frontend have same name. As backend path will have "ROOT/backend/../assets" name can be different to solve that added this fix. Which will create same named folders for backend and frontend. Folder names will be same.

```
        // Under components of config/main.php
        'assetManager' => [
            /*
             * when u have to make fix for images you will need it
             */
            'hashCallback'=>function( $path ){

                $path1 = $path ;
                $path = \common\components\Helper::getPathIdentical( $path );
                $hash = hash( 'md4' , $path );
                return $hash;
                
            },

            'bundles' => $bundlesFiles, // need to be added
        ],
```

### These extra functions added at "\common\components\Helper" ( Required )
* This are the function used in previous step so need to put them in "\common\components\Helper". 

```  
    // helper function should be added in helper file to achieve this.
    public static function getPathIdentical( $path )
    {
        if( strpos( $path , "_protected" ) !== false )
        {
            return self::sliceIt( $path ,  "_protected");
        }
        else if( strpos( $path , "themes" ) !== false )
        {
            return self::sliceIt( $path ,  "themes");
        }
        else
        {
            if( YII_DEBUG && strpos( $path , "minify-final" ) === false)
            {
                echo "Let Jaimin MosLake Know this happen. We made asset bundle with new directory. Add dependecny.";
                echo $path;
                exit;
            }
        }

        return $path ;
    }

    public static function sliceIt( $path , $sliceBy )
    {
        $explodeArray = explode( $sliceBy , $path );
        $backString = null;
        if( sizeof($explodeArray) >= 2 )
        {
            $backString = $sliceBy.$explodeArray[1];
        }

        return $backString;
    }

     
```

### My AssetMinifyController ( Console Controller ) ( Optional )

This will be helpfull if you have continuous deployment, now you want to generate all the assets from a console request and compress and concate them and upload them to S3 bucket. Just need to add this controller and call "php asset-minify/init" in continuous deployment script.


```
<?php

namespace console\controllers;


define('RUNNING_FROM_CONSOLE', true );


use common\assets\AppAssetVersion2;
use common\assets\HighchartsAsset;
use common\components\AppBasic;
use yii\console\Controller;
use processfast\yii\minify\View;
use common\assets\AppAsset;
use processfast\yii\minify\components\CSS;
use processfast\yii\minify\components\JS;
use Yii;
use yii\web\AssetBundle;

$url = "/dev/ops-insights/" ;
\Yii::setAlias('@webroot', \Yii::$app->basePath."/../../" );
\Yii::setAlias('@web', $url );


class AssetMinifyController extends Controller
{
    public function actionInit()
    {
        ini_set( 'max_execution_time' , 480 );

        $url = "/dev/ops-insights/" ;
        $webroot = \Yii::$app->basePath."/../../" ;
        $web = $url ;

        $view = new View();
        $view->S3Upload = true ;
        $view->awsBucket = 'aws-bucket-name' ;
        $view->assetsFolderPathPatch = '../../' ;
        $view->enableMinify = true ;
        $view->concatCss = true ; // concatenate css
        $view->minifyCss = true ; // minificate css
        $view->concatJs = true ; // concatenate js
        $view->minifyJs = true ; // minificate js
        $view->minifyOutput = true ; // minificate result html page
        $view->webPath = $web ;
        $view->basePath = $webroot ; // path alias to web base
        $view->minifyPath = $webroot.'/minify' ; // path alias to save minify result
        $view->jsPosition = [ \yii\web\View::POS_END ] ; // positions of js files to be minified
        $view->forceCharset = 'UTF-8' ; // charset forcibly assign, otherwise will use all of the files found charset
        $view->expandImports = true ; // whether to change @import on content
        $view->compressOptions = ['extra' => true]; // options for compress
        $view->excludeFiles = ['jquery.js', // exclude this file from minification
                                    'app-[^.].js', // you may use regexp
                                  ];
        $view->excludeBundles = [];
        $view->modifyPath = true ;
        $view->modifyPathData = '_protected/console/../../' ;

        $bundlesFiles = Yii::$app->params['bundles_minify'] ;
        $view->assetManager->bundles = $bundlesFiles ;


        $this->layout = "public_pages" ;
        $view->registerAssetBundle( AppAsset::className() );

        $assetBundle = $view->assetBundles ;
        // Revering it as it register asset bundle in reverse order. This array has reverse dependency
        // reversing array to give reverse dependency
        $assetBundle = array_reverse( $assetBundle );

        $this->assetBundleRegistration( $view ,  $assetBundle );
        (new CSS($view))->export();
        (new JS($view))->export();


        // exporting CSS/JS for new layout
        $this->layout = "main" ;
        $view->assetBundles = [] ;
        $view->cssFiles = [] ;
        $view->jsFiles = [] ;

        //AppAssetVersion2 is the main AssetBundle to "main" layout
        $view->registerAssetBundle( AppAssetVersion2::className() );

        $assetBundle = $view->assetBundles ;
        // Revering it as it register asset bundle in reverse order. This array has reverse dependency
        // reversing array to give reverse dependency
        $assetBundle = array_reverse( $assetBundle );

        $this->assetBundleRegistration( $view ,  $assetBundle );
        (new CSS($view))->export();
        (new JS($view))->export();

    }

    public function assetBundleRegistration( $view , $assetBundle)
    {
        foreach (array_keys( $assetBundle ) as $name) {

            if (!isset($view->assetBundles[$name])) {
                return;
            }
            $bundle = $view->assetBundles[$name];
            if ($bundle) {
                foreach ($bundle->depends as $dep) {
                    $this->assetBundleRegistration( $view , [$dep] );
                }
                $bundle->registerAssetFiles($view);
            }
            unset($view->assetBundles[$name]);
        }
    }

    public function registerBundle($view, $bundles, $name, &$registered)
    {
        if (!isset($registered[$name])) {
            $registered[$name] = false;
            $bundle = $bundles[$name];
            foreach ($bundle->depends as $depend) {
                $this->registerBundle($view, $bundles, $depend, $registered);
            }
            unset($registered[$name]);
            $registered[$name] = $bundle;
        } elseif ($registered[$name] === false) {
            throw new Exception("A circular dependency is detected for target.");
        }
    }


}

```



