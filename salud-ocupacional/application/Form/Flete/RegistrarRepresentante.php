<?php

class Form_Flete_RegistrarRepresentante extends Zend_Form{
    
    public function init(){
        $form_dec = new Formdecorator();

        $this->setName('frmRegistrarRepresentante');
        $this->setAttrib('accept-charset', 'utf-8');
        $this->setMethod('post');

        $hdnCodRepresentante = new Zend_Form_Element_Hidden('hdnCodRepresentante');
        $hdnCodRepresentante->setValue('99999999999');
        $this->addElement($hdnCodRepresentante);
        
        $txtRepresentante = new Zend_Form_Element_Text('txtRepresentante');
        $txtRepresentante->setLabel('Representante')
                ->setDescription("<span class='txtObligatorio'>*</span>")
                ->setAttrib("size", "20")
                ->setAttrib("maxlength", "60")
                ->setAttrib("class", "validate[required]");
        $this->addElement($txtRepresentante);
        
        $selPais = new Zend_Form_Element_Select('selPais');
        $selPais->setLabel('Pais:')
                ->setDescription("<span class='txtObligatorio'>*</span>");
        $this->addElement($selPais);
        
        $selCiudad = new Zend_Form_Element_Select('selCiudad');
        $selCiudad->setLabel('Ciudad:')
                ->addMultiOption('-','Seleccione')
                ->setDescription("<span class='txtObligatorio'>*</span>");
        $this->addElement($selCiudad);                
        
        $txtDireccion = new Zend_Form_Element_Text('txtDireccion');
        $txtDireccion->setLabel('Dirección:')
                ->setDescription("<span class='txtObligatorio'>*</span>")
                ->setAttrib("size", "20")
                ->setAttrib("maxlength", "60")
                ->setAttrib("class", "validate[required]");
        $this->addElement($txtDireccion);
        
        $txtContacto = new Zend_Form_Element_Text('txtContacto');
        $txtContacto->setLabel('Contacto:')
                ->setDescription("<span class='txtObligatorio'>*</span>")
                ->setAttrib("size", "20")
                ->setAttrib("maxlength", "60")
                ->setAttrib("class", "validate[required]");
        $this->addElement($txtContacto);
        
        $txtEmail = new Zend_Form_Element_Text('txtEmail');
        $txtEmail->setLabel('Email:')
                ->setDescription("<span class='txtObligatorio'>*</span>")
                ->setAttrib("size", "20")
                ->setAttrib("maxlength", "60")
                ->setAttrib("class", "validate[required]");
        $this->addElement($txtEmail);
        
        $txtTelefono = new Zend_Form_Element_Text('txtTelefono');
        $txtTelefono->setLabel('Teléfono:')
                ->setDescription("<span class='txtObligatorio'>*</span>")
                ->setAttrib("size", "20")                
                ->setAttrib("maxlength", "60")
                ->setAttrib("class", "validate[required]");
        $this->addElement($txtTelefono);
        
        $txtFax = new Zend_Form_Element_Text('txtFax');
        $txtFax->setLabel('Fax:')
                ->setDescription("<span class='txtObligatorio'>*</span>")
                ->setAttrib("size", "20")                
                ->setAttrib("maxlength", "60")
                ->setAttrib("class", "validate[required]");
        $this->addElement($txtFax);
        
        $form_dec->formDecoratorTable($hdnCodRepresentante, false, 'left', 1, false, false);
        $form_dec->formDecoratorTable($txtRepresentante, true, 'left', 1, true, true);
        $form_dec->formDecoratorTable($selPais, true, 'left', 1, true, true);
        $form_dec->formDecoratorTable($selCiudad, true, 'left', 1, true, true);
        $form_dec->formDecoratorTable($txtDireccion, true, 'left', 1, true, true);
        $form_dec->formDecoratorTable($txtContacto, true, 'left', 1, true, true);
        $form_dec->formDecoratorTable($txtEmail, true, 'left', 1, true, true);
        $form_dec->formDecoratorTable($txtTelefono, true, 'left', 1, true, true);
        $form_dec->formDecoratorTable($txtFax, true, 'left', 1, true, true);        
                
        $this->addDisplayGroup(array('hdnCodRepresentante', 'txtRepresentante','selPais','selCiudad','txtDireccion','txtContacto',
            'txtEmail','txtTelefono','txtFax'), 'frmRegistrarRepresentante', array(
            'order'=>0,
            'decorators' => array(
                'FormElements',
                'Fieldset',
                array('HtmlTag', array('tag' => 'table', 'id' => 'tblRegistrarRepresentante')),
            ),
        ));
    }
}