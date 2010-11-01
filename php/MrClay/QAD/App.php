<?php

/**
 * Proof of concept to allow creating a ZF app from a single file using
 * view and layout objects that get content from function calls instead of
 * script includes
 */
class MrClay_QAD_App {

    public $options = null;

    public $controllerInvokeArgs = array(
        'displayExceptions' => false,
    );

    public function getDefaultOptions()
    {
        return array(
            'layoutCallback' => array('MrClay_QAD_Layout_Default', 'layout'),
            'viewFunctionPrefix' => 'view_',
            'prependPathInfo' => '/index',
        );
    }

    public function __construct($options = array())
    {
        $this->options = array_merge($this->getDefaultOptions(), $options);
    }

    public function run()
    {
        $includedFiles = get_included_files();
        $appDir = dirname($includedFiles[0]);

        $front = Zend_Controller_Front::getInstance();
        $front->setParams($this->controllerInvokeArgs);

        $view = new MrClay_QAD_View(array(
            'scriptPath' => $appDir . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'scripts',
        ));
        $view->setFunctionPrefix($this->options['viewFunctionPrefix']);
        $viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer($view);
        Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);

        if (! class_exists('ErrorController', false)) {
            // wish there were a better way...
            eval('class ErrorController extends MrClay_QAD_ErrorController {}');
        }

        if (is_string($this->options['layoutCallback']) && !function_exists($this->options['layoutCallback'])) {
            $this->options['layoutCallback'] = false;
        }
        if ($this->options['layoutCallback']) {
            $layout = new MrClay_QAD_Layout();
            $layout->setCallback($this->options['layoutCallback']);
            $layoutPlugin = new Zend_Layout_Controller_Plugin_Layout($layout);
            $front->registerPlugin($layoutPlugin);
        }
        if (! empty($this->options['prependPathInfo'])) {
            $request = new Zend_Controller_Request_Http();
            $request->setPathInfo($this->options['prependPathInfo'] . $request->getPathInfo());
            $front->setRequest($request);
        }

        $front->run($appDir . DIRECTORY_SEPARATOR . 'controllers');
    }
}
