<?php
namespace ATW\PageMockups\Controllers;

use Exception;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\ORM\ArrayList;
use ATW\PageMockups\Models\MockupPage;
use SilverStripe\View\SSViewer;

class MockupController extends Controller
{

    private static $allowed_actions = [
        'index'
    ];

    private static $url_handlers = [
        '$URLSegment' => 'index',
    ];

    protected $pages = null;
    protected $data = null;

    protected function loadConfig() {
        $pages_config = $this->config()->get("pages");
//        print_r($pages_config);
        $pages = new ArrayList();
        foreach($pages_config as $config) {
            $page = new MockupPage($config);
            $pages->push($page);
        }
        $this->pages = $pages;
    }

    public function Page($urlSegment) {
        return $this->pages->find('URLSegment', $urlSegment);
    }

    public function Link($action = null)
    {
        return $this->data->Link(($action ? $action : true));
    }

    public function index(HTTPRequest $request)
    {
        $this->loadConfig();
        $urlSegment = $request->param('URLSegment');
        if(!$urlSegment)
            $urlSegment = "home";

        $this->data = $this->Page($urlSegment);

        if(!$this->data)
            return $this->httpError(404);

        $this->data->LinkOrSection = 'section';
        $this->setFailover($this->data);
        $templates = SSViewer::get_templates_by_class($this->data->Type);
        return $this->renderWith($templates);
    }

    public function Menu($level) {
        $menu = [];
        foreach($this->pages as $page) {
            if(!$page->Hidden)
                $menu[] = $page;
        }
        return new ArrayList($menu);
    }

}
