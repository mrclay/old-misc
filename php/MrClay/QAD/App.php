<?php

/**
 * Proof of concept to allow creating a ZF app from a single file using
 * view and layout objects that get content from function calls instead of
 * script includes
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class MrClay_QAD_App {

    /**
     * @var array
     */
    public $options = null;

    public $controllerInvokeArgs = array(
        'displayExceptions' => false,
    );

    public function getDefaultOptions()
    {
        $includedFiles = get_included_files();
        $appPath = dirname(dirname($includedFiles[0])) . DIRECTORY_SEPARATOR . 'application';
        return array(
            'applicationPath' => $appPath,
            'controllersPath' => 'APPLICATION_PATH' . DIRECTORY_SEPARATOR . 'controllers',
            'viewsPath' => 'APPLICATION_PATH' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'scripts',
            'layoutPath' => 'APPLICATION_PATH' . DIRECTORY_SEPARATOR . 'layout' . DIRECTORY_SEPARATOR . 'scripts',
            'useLayout' => true,
            'layoutCallback' => false,
            'viewFunctionPrefix' => 'view_',
            'layoutFunctionPrefix' => 'layout_',
            'prependPathInfo' => '/index',
            'requireViews' => false,
        );
    }

    public function __construct($options = array())
    {
        $this->options = array_merge($this->getDefaultOptions(), $options);
    }

    public function run()
    {
        // resolve resource paths
        $appPath = $this->options['applicationPath'];
        foreach (array('layout', 'controllers', 'views') as $key) {
            $this->options["{$key}Path"] = str_replace('APPLICATION_PATH', $appPath, $this->options["{$key}Path"]);
        }

        $front = Zend_Controller_Front::getInstance();
        $front->setParams($this->controllerInvokeArgs);

        $view = new MrClay_QAD_View(array('requireRenderer' => $this->options['requireViews']));
        $view->setScriptPath($this->options['viewsPath']);
        $resolver = new MrClay_QAD_View_CallbackResolver($this->options['viewFunctionPrefix']);
        $view->setCallbackResolver($resolver);
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        $errorViewCallback = $resolver->resolve('error/error.phtml');
        if (is_string($errorViewCallback) && ! function_exists($errorViewCallback)) {
            // :(
            eval("function $errorViewCallback(Zend_View \$view)"
               . "{MrClay_QAD_ErrorController::defaultView(\$view);}");
        }
        if (! class_exists('ErrorController', false)) {
            // :(
            eval('class ErrorController extends MrClay_QAD_ErrorController {}');
        }

        if ($this->options['useLayout']) {
            $layout = new Zend_Layout();
            $layout->setViewScriptPath($this->options['layoutPath']);
            $layoutView = new MrClay_QAD_View();
            $layoutResolver = new MrClay_QAD_View_CallbackResolver($this->options['layoutFunctionPrefix']);
            if ($this->options['layoutCallback']) {
                $layoutResolver->setCallback($this->options['layoutCallback']);
            }
            $layoutView->setCallbackResolver($layoutResolver);
            $layout->setView($layoutView);
            $front->registerPlugin(new Zend_Layout_Controller_Plugin_Layout($layout));
        }

        if (! empty($this->options['prependPathInfo'])) {
            $request = new Zend_Controller_Request_Http();
            $request->setPathInfo($this->options['prependPathInfo'] . $request->getPathInfo());
            $front->setRequest($request);
        }

        $front->run($this->options['controllersPath']);
    }
}
