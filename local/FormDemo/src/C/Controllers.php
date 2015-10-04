<?php
namespace C\FormDemo;

use Silex\Application;
use C\ModernApp\Dashboard\Transforms as Dashboard;
use Symfony\Component\HttpFoundation\Request;
use C\ModernApp\File\Transforms as FileLayout;

class Controllers {

    public function index() {
        return function (Application $app) {

            $generator = $app['url_generator'];

            /* @var $form \Symfony\Component\Form\Form*/
            $form = $app['form.factory']
                ->createBuilder(new DemoForm(), ["email"=>"some@mail.com"])
                ->setAction($generator->generate("form_demo_post", []))
                ->setMethod('POST')
                ->getForm();

            FileLayout::transform($app)
                ->importFile("HTML:/1-column.yml")
                ->addIntl('body_content', 'FormDemo:/en.yml', 'en')
                ->setTemplate('body_content', __DIR__.'/templates/form-demo.php')
                ->updateData('body_content', [
                    'form'=>$form->createView()
                ])
                ->then(Dashboard::transform($app)->forRequest('get')->show());
            return $app['layout']->render();
        };
    }

    public function submit() {
        return function (Application $app, Request $request) {

            $generator = $app['url_generator'];

            /* @var $form \Symfony\Component\Form\Form*/
            $form = $app['form.factory']
                ->createBuilder(new DemoForm(), ["email"=>"some"])
                ->setAction($generator->generate("form_demo_post", []))
                ->setMethod('POST')
                ->getForm();

            /* @var $form \Symfony\Component\Form\Form*/
            $form->handleRequest($request);

            if ($form->isValid()) {
                $data = $form->getData();
                var_dump($data);

                $nextAction = $form->get('save')->isClicked()
                    ? 'save'
                    : 'post';

                var_dump($nextAction);
            }
            var_dump($form->isValid());

            FileLayout::transform($app)
                ->importFile("HTML:/1-column.yml")
                ->setTemplate('body_content', __DIR__.'/templates/form-demo.php')
                ->updateData('body_content', [
                    'form'=>$form->createView()
                ])
                ->then(Dashboard::transform($app)->forRequest('get')->show());
            return $app['layout']->render();
        };
    }

}