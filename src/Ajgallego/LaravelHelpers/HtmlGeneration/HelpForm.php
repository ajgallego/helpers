<?php namespace Ajgallego\LaravelHelpers\HtmlGeneration;

use Illuminate\Support\Facades\Form;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Session;

class HelpForm
{
    //-------------------------------------------------------------------------
    public static function separation( $height = '8' ) 
    {
        return '<div style="height:'. $height .'px"></div>';
    }

    //-------------------------------------------------------------------------
    public static function help( $comment )
    {
        return '<div class="control-group">'
               .'<div class="controls">'
               .  '<span class="help-inline">'. $comment .'</span>'
               .'</div></div>';
    }

    //-------------------------------------------------------------------------
    public static function startFieldset( $title )
    {
        return '<fieldset><legend>'.$title.'</legend>';
    }

    //-------------------------------------------------------------------------
    public static function endFieldset()
    {
        return '</fieldset>';
    }

    //-------------------------------------------------------------------------
    public static function checkbox( $name, $label, $selected = true, 
                                     $isRequired=false, $helpText='',
                                     $textAsPrefix=true )
    {
        $printLabel = true;
        $strChecked = '';
        $arrayInputOld = Input::old();
        $oldValue = Input::old($name, false);

        if( ( empty($arrayInputOld) == true && $selected == true ) || 
            ( empty($arrayInputOld) == false && $oldValue ) )        
        {
            $strChecked = ' checked="checked"';
        }

        $strField = '<input id="'. $name .'" name="'. $name .'" type="checkbox" value="1"'. $strChecked .'/>';

        if( $textAsPrefix == false )
        {
            $labelRequired = ( $isRequired == true ? ' required' : '' );

            $strField .= '<label class="checkbox inline label-as-suffix'. $labelRequired .'" for="'. $name .'">'
                        .   $label
                        .'</span>';
            $printLabel = false;
        }

        return self::prv_printField( $name, $label, $strField, $isRequired, $helpText, $printLabel );
    }

    //-------------------------------------------------------------------------
    public static function select( $name, $label, $arrayOptions, $selected = '', 
                                   $isRequired=false, $placeholder = '', $helpText='' ) 
    {
        $emptyOption = array();
        $emptyOption[''] = 'Elige una opción...';
        $arrayOptions = $emptyOption + $arrayOptions;

        $strField = Form::select( $name, 
                          $arrayOptions, 
                          Input::old( $name, $selected ),
                          array( 'placeholder' => $placeholder, 
                                 'required' => ( $isRequired ? 'true' : 'false') 
                                 ) );

        return self::prv_printField( $name, $label, $strField, $isRequired, $helpText );
    }

	/**
    * Adds a multiselect field. It uses the chosen library.
    */
    public static function multiselect( $name, $label, $options, $selected = '', 
                                        $isRequired=false, $placeholder = '', $helpText='' ) 
    {
        $strField = Form::select( $name . '[]', 
                          $options, 
                          Input::old( $name, $selected ),
                          array( 'multiple' => 'true', 
                                 'data-rel' => 'chosen', 
                                 'class' => 'form-control', 
                                 'data-placeholder' => $placeholder
                                 //'required' => ( $isRequired ? 'true' : 'false') // error when submit
                                 ) );

    	return self::prv_printField( $name, $label, $strField, $isRequired, $helpText );
	}

    //-------------------------------------------------------------------------
    public static function combobox( $name, $label, $options, $selected = '', 
                                     $isRequired=false, $placeholder = '', $helpText='' ) 
    {
        $strPlaceholder = ( $placeholder != '' ? ' placeholder="'. $placeholder .'"' : '' );
        $strInputRequired = ( $isRequired == true ? ' required="true"' : '');
        $strValue = Input::old( $name, $selected );

        $strField = '<div class="input-append dropdown combobox">'
                    .   '<input class="input-large combobox-input" type="text" autocomplete="off" '
                    .       'value="'. $strValue .'" '
                    .       'name="'. $name .'" id="'. $name .'"'. $strPlaceholder . $strInputRequired . '/>'
                    .   '<span class="add-on btn" data-toggle="dropdown"><i class="caret"></i></span>';

        if( count($options) > 0 )
        {
            $strField .= '<ul class="dropdown-menu">';

            foreach( $options as $option )
            {
                $strField .= '<li><a href="#">'. $option[ $name ] .'</a></li>';
            }

            $strField .= '</ul>';
        }

        $strField .= '</div>';

        return self::prv_printField( $name, $label, $strField, $isRequired, $helpText );
    }

    //-------------------------------------------------------------------------
    // http://www.jasny.net/articles/jasny-bootstrap-file-upload-with-existing-file/
    public static function file( $name, $label, $selected = '', $isRequired=false, $helpText='' ) 
    {
        $inputRequired = ( $isRequired == true ? 'required="true"' : '' );
        $fileuploadClass = ( $selected == '' ? 'fileupload-new' : 'fileupload-exists' );

        $strField = '<div class="fileupload '. $fileuploadClass .'" data-provides="fileupload">'
                    .   '<div class="input-append">'
                    .       '<div class="uneditable-input span3">'
                    .           '<i class="icon-file fileupload-exists"></i> <span class="fileupload-preview">'. $selected .'</span>'
                    .       '</div>'
                    .       '<span class="btn btn-file">'
                    .           '<span class="fileupload-new">Elegir fichero</span>'
                    .           '<span class="fileupload-exists">Cambiar</span>'
                    .           '<input type="file" name="'. $name .'" id="'. $name .'" '. $inputRequired .'/>'
                    .       '</span>'
                    .       '<a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Eliminar</a>'
                    .   '</div>'
                    .'</div>';

        return self::prv_printField( $name, $label, $strField, $isRequired, $helpText );
    }

    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------
    // PRIVATE
    //-------------------------------------------------------------------------
    //-------------------------------------------------------------------------

    //-------------------------------------------------------------------------
    private static function prv_getLabel( $name, $label, $isRequired )
    {
        $labelRequired = ( $isRequired == true ? ' required' : '' );

        return Form::label( $name , $label, array('class' => 'control-label col-lg-2 col-sm-4' . $labelRequired ) );
    }

    //-------------------------------------------------------------------------
    private static function prv_printField( $name, $label, $strField, $isRequired,
                                            $helpText='', $printLabel=true )
    {
        $strLabel = '';
        if( $printLabel )
            $strLabel = self::prv_getLabel( $name, $label, $isRequired );

        $errors = Session::get('errors', null );

        $classRequired = ( $isRequired == true ? ' required' : '' );
        $style_error = '';
        $msg_error = '';
        $msg_help = '';

        if( isset($errors) && $errors->has( $name ) )
        {
            $style_error = ' error';
            $msg_error = '<span class="help-inline" style="vertical-align:top;padding-top:5px">' . $errors->first( $name ) . '</span>';
        }
        else if( $helpText != '' )
        {
            $msg_help = '<span class="help-inline">'. $helpText .'</span>';
        }

        return '<div class="form-group'. $style_error . $classRequired . '">'

                . $strLabel

                .'<div class="col-lg-10 col-sm-8">'

                .   $strField

                .   $msg_error 

                .   $msg_help

                .'</div>'

                .'</div>' 
                ."\n";
    }
}
