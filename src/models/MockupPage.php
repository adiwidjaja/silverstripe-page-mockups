<?php
namespace ATW\PageMockups\Models;
use Exception;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;

class MockupPage extends MockupData {

    protected $MockupElements = null;

    public function __construct($value = []) {
        parent::__construct($value);

        $this->Type = "Page";
    }

    public function getMenuTitle()
    {
        if ($value = $this->getField("MenuTitle")) {
            return $value;
        } else {
            return $this->getField("Title");
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
