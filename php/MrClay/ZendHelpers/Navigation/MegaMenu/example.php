<?php
die();

// setup your autoloading here...

$menu = new Zend_Navigation(array(

    array(
        'uri' => '/', // required
        'label' => '<big>Home</big>', // required
        'labelIsHtml' => true, // (optional) don't escape this label
        'liAttrs' => array( // optional attributes for the LI
            'class' => 'ie-list-first menu-home-icon'
        ),
    ),

    array(
        'uri' => '/about/',
        'label' => 'About',
        'ulAttrs' => array( // optional attributes for the child UL
            'id' => 'myUlId',
            'class' => 'myUlClass',
        ),
        'beforeUl' => "Hello World!", // optional HTML placed before the child UL
        'afterUl' => "Goobdye World!", // optional HTML after the child UL
        'pages' => array(

            array(
                'uri' => '/about/us',
                'label' => 'About Us',
            ),

            array(
                'uri' => '/about/you/',
                'label' => 'About You',
            ),

        ),
    ),

    array(
        'uri' => '/products/',
        'label' => 'Products',
        'pages' => array(

            array(
                'uri' => '/products/big/',
                'label' => 'Big Products',
                'pages' => array(

                    array(
                        'uri' => '/products/cars/',
                        'label' => 'Cars',
                    ),

                    array(
                        'uri' => '/products/boats/',
                        'label' => 'Boats',
                    ),

                ),
            ),

            array(
                'uri' => '/products/small/',
                'label' => 'Small Products',
            ),

        ),
    ),

));

if ($found = $menu->findBy('label', 'Cars')) {
    $found->setActive();
}

$helper = new MrClay_ZendHelpers_Navigation_MegaMenu();
echo $helper->render($menu);