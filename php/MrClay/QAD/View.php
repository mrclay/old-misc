<?php

/**
 * View that renders via calling a callback function instead of including a
 * script.
 *
 * @author Steve Clay <steve@mrclay.org>
 * @license http://www.opensource.org/licenses/mit-license.php  MIT License
 */
class MrClay_QAD_View extends Zend_View {

    /**
     * @var MrClay_QAD_View_CallbackResolver
     */
    protected $__qad_callbackResolver = null;

    protected $__qad_requireRenderer = true;

    /**
     * @param MrClay_QAD_View_CallbackResolver $resolver
     * @return MrClay_QAD_View
     */
    public function setCallbackResolver(MrClay_QAD_View_CallbackResolver $resolver = null)
    {
        if (null === $resolver) {
            $resolver = new MrClay_QAD_View_CallbackResolver();
        }
        $this->__qad_callbackResolver = $resolver;
        return $this;
    }

    /**
     * @return MrClay_QAD_View_CallbackResolver
     */
    public function getCallbackResolver()
    {
        if (null === $this->__qad_callbackResolver) {
            $this->setCallbackResolver();
        }
        return $this->__qad_callbackResolver;
    }

    public function __construct($config = array())
    {
        if (array_key_exists('callbackResolver', $config)) {
            $this->setCallbackResolver($config['callbackResolver']);
            $config['callbackResolver'] = null;
        }
        if (array_key_exists('requireRenderer', $config)) {
            $this->__qad_requireRenderer = $config['requireRenderer'];
            $config['requireRenderer'] = null;
        }
        parent::__construct($config);
    }

    /**
     * Take a view script name and try to get the output from a corresponding
     * view function.
     *
     * If the function is missing, try regular script rendering.
     *
     * @param string $name The script name to process.
     * @return string output from a function/script
     */
    public function render($name)
    {
        // try callback
        $callback = $this->getCallbackResolver()->resolve($name);
        if (is_callable($callback)) {
            return $this->_renderCallback($callback);
        }
        // try file
        if ($this->__qad_requireRenderer) {
            // don't catch exceptions
            return parent::render($name);
        } else {
            try {
                return parent::render($name);
            } catch (Zend_View_Exception $e) {
                // silent
            }
            return '';
        }
    }

    protected function _renderCallback($callback)
    {
        ob_start();
        call_user_func($callback, $this);
        return ob_get_clean();
    }

    protected function _run()
    {
        include func_get_arg(0);
    }
}