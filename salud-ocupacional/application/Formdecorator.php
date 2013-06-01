<?php 		
class Formdecorator extends Zend_Form_Decorator_Abstract{
    function formDecoratorTable($element, $showlabel=true, $align = 'left', $colspan = 1, $openOnly = false, $closeOnly = false){
        $aDecorator = array(
            'ViewHelper',
            array('Description',array('tag'=>'','escape'=>false)),
            'Errors',
            array(array('data'=>'HtmlTag'), array('tag' => 'td', 'colspan'=> $colspan, 'align'=> $align ))
        );

        if ($showlabel){
            $aDecorator[] = array('Label', array('tag' => 'td'));
        }
        if ($openOnly || $closeOnly){
           $aDecorator[] =  array(array('row'=>'HtmlTag'),array('tag'=>'tr', 'openOnly'=> $openOnly, 'closeOnly'=> $closeOnly ));
        }
        $element->setDecorators($aDecorator);
    }
}