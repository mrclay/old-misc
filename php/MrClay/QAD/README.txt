WHAT IS "QAD"?

QAD is a set of classes to enable the creation of a "quick and dirty" Zend
Framework app within a single file without changing how the controllers/views
work too much. Zend_View is simply extended to support using a callback function
to render output before using a script include.

Consider it an alpha proof of concept. Ideally something created this way can be
trivially turned into a full ZF app if need be.


EXAMPLE SCRIPT

    // setup autoload...

    // any controllers not defined before $app->run() will be loaded from
    // $app->options['controllersPath']
    class IndexController extends Zend_Controller_Action {

        public function indexAction() {}      // handles /

        public function hyphenAtedAction() {} // handles /hyphen-ated
    }

    // missing views will be fetched from $app->options['viewsPath']
    function view_index_index(Zend_View $view) {
        echo "Hello";
    }
    function view_index_hyphen__ated(Zend_View $view) {
        echo "World!";
    }

    // missing layouts will be fetched from $app->options['layoutPath']
    function layout_layout($view) {
        echo $view->render('header.phtml'); // use script names...
        echo $view->layout()->content;
        echo $view->render('the-footer.phtml');
    }
    function layout_header(Zend_View $view) {
        echo "header!<br>";
    }
    function layout_the__footer(Zend_View $view) {
        echo "<br>the footer!";
    }

    $app = new MrClay_QAD_App();
    $app->controllerInvokeArgs['displayExceptions'] = true;
    $app->run();



NOTES

Notice "/hyphen-ated" calls the index controller. This is because, by default,
QAD prepends the pathInfo of the request with "/index". You can disable this:

    $app = new MrClay_QAD_App(array('prependPathInfo' => ''));

If you don't define ErrorController or view_error_error() before running the app,
QAD creates them at runtime based on MrClay_QAD_ErrorController(::defaultView).

The $options passed into MrClay_QAD_App's constructor allow you to:

  * disable layout (useLayout = false)
  * specify a callback for layout (layoutCallback = 'my_layout')
  * change the prefixes used to lookup view/layout functions
  * throw exception if view is missing (requireViews = true)


IMPLEMENTATION

Into the front controller and layout objects, the app injects custom views which
behave mostly identically to Zend_View until the rendering stage.

In render(), MrClay_QAD_View uses a callback resolver to convert a script name
like "foo/bar-ding.phtml" into a callback function like "view_foo_bar__ding".
If callable, the function is called and render() captures the output from it,
passing the view in as an argument.

If the function is missing, it tries the regular script rendering of Zend_View,
looking for scripts in the your 'viewsPath' directory.


APP DIRECTORIES

Though no directories are required, QAD will sniff out missing controllers,
views, and layout scripts, assuming a typical ZF directory setup:

/application
   /controllers
   /views/scripts
   /layout/scripts
/public
   index.php  <-- your script
   .htaccess  <-- use standard mod_rewrite rules for nicer URLs

In $options, you can customize all these paths if you wish.
