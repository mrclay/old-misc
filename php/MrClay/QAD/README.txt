QAD is a set of classes to enable the creation of a "quick and dirty" Zend
Framework app within a single file without changing how the controllers/views
work too much. Consider it an alpha proof of concept. Ideally something created
this way can be trivially turned into a full ZF app if need be.

The view and layout classes use function calls to render output instead of
script includes. E.g. this could be in a single file:

----

    // setup autoloading ...

    // define controller(s)
    // (if you need more than one you probably should not be using this)

    class IndexController extends Zend_Controller_Action {

        // handles /
        public function indexAction()
        {
            $this->view->hello = ', World!';
        }

        // handles /there
        public function thereAction()
        {
            $this->view->hello = ' there!';
            $this->render('index'); // use index's view
        }
    }

    // define (optional) view functions

    function view_index_index(Zend_View $view) {
        echo "Hello" . $view->escape($view->hello);
    }

    $app = new MrClay_QAD_App();
    $app->run();

----

Notice "/there" calls the index controller. This is because, by default, QAD
prepends the pathInfo of the request with "/index". You can disable this:

    $app = new MrClay_QAD_App(array('prependPathInfo' => ''));

You can also allow displaying exception traces in the default error controller:

    $app->controllerInvokeArgs['displayExceptions'] = true;

BTW, if you don't define ErrorController before running the app, QAD creates
one at runtime extending MrClay_QAD_ErrorController.

----

The app injects custom views and layouts into the controllers, that behave
mostly identically to Zend_View and Zend_Layout until the rendering stage.

MrClay_QAD_View converts the script name it's given (e.g. "foo/bar.phtml")
into a function name "view_foo_bar". If the function exists, it captures the
output from it, passing the view in as an argument. If the function is missing,
it tries regular script rendering, looking for scripts in the ./views/scripts
directory (path from the executing script)

MrClay_QAD_Layout similarly renders the output of a callback function.

You can also create a ./controllers directory and put controllers there and the
Zend dispatcher will check there for them.
