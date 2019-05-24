<?php
namespace ATW\PageMockups\Models;
use Exception;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;

class MockupPage extends ArrayData {

    protected $MockupElements = null;

    public function __construct($value = []) {
        parent::__construct($value);

        if(!$this->Type)
            $this->Type = "Page";

        foreach($value as $name => $value) {
            if (is_array($value)) {
                $list = new ArrayList();
                foreach($value as $childData) {
                    $child = new MockupPage($childData);
                    $list->push($child);
                }
                $this->$name = $list;
            }
        }
    }

    public function getMenuTitle()
    {
        if ($value = $this->getField("MenuTitle")) {
            return $value;
        } else {
            return $this->getField("Title");
        }
    }

    function isHtml($string) {
        return preg_match("/<[^<]+>/",$string,$m) != 0;
    }

    public function getField($field) {
        if(!array_key_exists($field, $this->array))
            return;
        $value = parent::getField($field);
        if($value && $this->isHtml($value)) {
            /** @var DBHTMLText $html */
            $html = DBField::create_field('HTMLFragment', $value);
            return $html;
        } else {
            return $value;
        }
    }

    public function Link() {
        return "mockups/".$this->URLSegment;
    }

    public function ElementalArea() {
        if($this->Elements) {
            $output = "";
            foreach($this->Elements as $element) {
                $output.= $element->render();
            }
            /** @var DBHTMLText $html */
            $html = DBField::create_field('HTMLFragment', $output);
            return $html;
        }
    }


    public function render() {
        $templates = SSViewer::get_templates_by_class($this->Type);
        return $this->renderWith($templates);
    }

    function startsWith($haystack, $needle)
    {
        $length = strlen($needle);
        return (substr($haystack, 0, $length) === $needle);
    }

    function Mix($path, $manifestDirectory = 'app/client/dist')
    {
        static $manifest;
        $rootPath = BASE_PATH;
        $publicPath = $rootPath;
        if ($manifestDirectory && ! $this->startsWith($manifestDirectory, '/')) {
            $manifestDirectory = "/{$manifestDirectory}";
        }
        if (! $manifest) {
            if (! file_exists($manifestPath = ($rootPath . $manifestDirectory.'/mix-manifest.json') )) {
                throw new Exception('The Mix manifest does not exist.');
            }
            $manifest = json_decode(file_get_contents($manifestPath), true);
        }
        if (! $this->startsWith($path, '/')) {
            $path = "/{$path}";
        }
        if (! array_key_exists($path, $manifest)) {
            throw new Exception(
                "Unable to locate Mix file: {$path}. Please check your ".
                'webpack.mix.js output paths and try again.'
            );
        }
        return file_exists($publicPath . ($manifestDirectory.'/hot'))
            ? "http://localhost:3000/resources/app/client/dist".$manifest[$path]
            : "resources/app/client/dist".$manifest[$path];
    }

}
