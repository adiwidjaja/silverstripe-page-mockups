<?php
namespace ATW\PageMockups\Models;
use Exception;
use SilverStripe\ORM\ArrayLib;
use SilverStripe\ORM\ArrayList;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\ORM\FieldType\DBHTMLText;
use SilverStripe\View\ArrayData;
use SilverStripe\View\SSViewer;

class MockupData extends ArrayData {

    public function __construct($value = []) {
        parent::__construct($value);

        if(!$this->Type)
            $this->Type = "Data";

        foreach($value as $name => $value) {
            if (ArrayLib::is_associative($value)) {
                $this->$name = new ArrayData($value);
            } elseif (is_array($value)) {
                $list = new ArrayList();
                foreach($value as $childData) {
                    $child = new MockupData($childData);
                    $list->push($child);
                }
                $this->$name = $list;
            }
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
    public function getTemplates($class, $suffix = '') {
        //Simplified SSViewer::get_templates_by_class
        $template = $class . $suffix;
        $templates[] = $template;
        $templates[] = ['type' => 'Includes', $template];

        // If the class is "PageController" (PSR-2 compatibility) or "Page_Controller" (legacy), look for Page.ss
        if (preg_match('/^(?<name>.+[^\\\\])_?Controller$/iU', $class, $matches)) {
            $templates[] = $matches['name'] . $suffix;
        }
        return $templates;
    }


    public function render() {
        $templates = $this->getTemplates($this->Type);
        return $this->renderWith($templates);
    }

}
