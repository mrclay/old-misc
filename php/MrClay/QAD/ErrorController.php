<?php

class MrClay_QAD_ErrorController extends Zend_Controller_Action {
    
    public function errorAction()
    {
        $errors = $this->_getParam('error_handler');
        switch ($errors->type) {
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ROUTE:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_CONTROLLER:
            case Zend_Controller_Plugin_ErrorHandler::EXCEPTION_NO_ACTION:

                // 404 error -- controller or action not found
                $this->getResponse()->setHttpResponseCode(404);
                $this->view->message = 'Page not found';
                break;
            default:
                // application error
                $this->getResponse()->setHttpResponseCode(500);
                $this->view->message = 'Application error';
                break;
        }

        // Log exception, if logger available
        if ($log = $this->getInvokeArg('logger')) {
            $log->crit($this->view->message, $errors->exception);
        }

        // conditionally display exceptions
        if ($this->getInvokeArg('displayExceptions') == true) {
            $this->view->exception = $errors->exception;
        }

        $this->view->request = $errors->request;
    }

    public static function defaultView(Zend_View $view)
    {
        ?><h1>An error occurred</h1>
  <h2><?php echo $view->message; ?></h2>

  <?php if (isset($view->exception)): ?>

  <h3>Exception information:</h3>
  <p>
      <b>Message:</b> <?php echo $view->exception->getMessage() ?>
  </p>

  <h3>Stack trace:</h3>
  <pre><?php echo $view->exception->getTraceAsString() ?>
  </pre>

  <h3>Request Parameters:</h3>
  <pre><?php echo var_export($view->request->getParams(), true) ?>
  </pre>
  <?php endif ?><?php
    }
}