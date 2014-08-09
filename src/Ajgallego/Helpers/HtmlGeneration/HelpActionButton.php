<?php 

namespace Ajgallego\Helpers\HtmlGeneration;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Form;

/**
* Helper to create actions buttons: show, create, edit, delete, back or custom buttons.
* It also allows to create small buttons or to add styles, classes or attibutes.
*/
class HelpActionButton
{
    private $mIcon           = '';
    private $mLabel          = ''; 
    private $mDataUri        = '';
    
    private $mStyles         = array();
    private $mClasses        = array();
    private $mAttributes     = array();

    private $mSmall          = false;
    private $mIsDeleteButton = false;


    /**
    * Private action button constructor
    * @param string $data_uri The uri to your resource, it is used to generate the button actions routes
    */
    private function __construct( $_icon, $_label, $_uri, $_classes, $_is_delete_button = false )
    {
        $this->mIcon = $_icon;
        $this->mLabel = $_label;
        $this->mDataUri = $_uri;
        $this->mClasses[] = $_classes;
        $this->mIsDeleteButton = $_is_delete_button;
        return $this;
    }

    /**
    * Create a show button
    * @param string $data_uri Used to generate the edit, delete and return buttons
    * @param integer $resource_id Used to generate the edit and delete buttons
    */
    public static function show( $_data_uri, $_resource_id )
    {
        return new HelpActionButton( '<i class="fa fa-eye"></i>', 
                                     trans('forms.actions.show'), 
                                     URL::route( $_data_uri .'.show', $_resource_id ), 
                                     'btn btn-success' );
    }

    /**
    * Create a new button
    * @param string $data_uri Used to generate the edit, delete and return buttons
    */
    public static function create( $_data_uri )
    {
        return new HelpActionButton( '<i class="fa fa-plus-circle"></i>',
                                     trans('forms.actions.create'), 
                                     URL::route( $_data_uri .'.create'), 
                                     'btn btn-success' );
    }

    /**
    * Create an edit button
    * @param string $data_uri Used to generate the edit, delete and return buttons
    * @param integer $resource_id Used to generate the edit and delete buttons
    */
    public static function edit( $_data_uri, $_resource_id )
    {
        return new HelpActionButton( '<i class="fa fa-pencil"></i>', 
                                     trans('forms.actions.edit'), 
                                     URL::route( $_data_uri .'.edit', $_resource_id ), 
                                     'btn btn-success' );
    }

    /**
    * Create a delete button
    * @param string $data_uri Used to generate the edit, delete and return buttons
    * @param integer $resource_id Used to generate the edit and delete buttons
    */
    public static function delete( $_data_uri, $_resource_id )
    {
        return new HelpActionButton( '<i class="fa fa-trash-o"></i>', 
                                     trans('forms.actions.delete'), 
                                     URL::route( $_data_uri .'.destroy', $_resource_id ), 
                                     'btn btn-danger', 
                                     true ); // is delete button
    }

    /**
    * Create a return button
    * @param string $data_uri Used to generate the edit, delete and return buttons
    */
    public static function back( $_data_uri )
    {
        return new HelpActionButton( '<i class="fa fa-chevron-left"></i>', 
                                     trans('forms.actions.return'), 
                                     URL::route( $_data_uri .'.index' ), 
                                     'btn btn-default' );
    }

    /**
    * Create a custom button
    * @param string $_icon The icon to show
    * @param integer $_label The label of the button
    * @param string $_uri The action uri of the button
    */
    public static function custom( $_icon, $_label, $_uri )
    {
        return new HelpActionButton( $_icon, 
                                     $_label, 
                                     $_uri, 
                                     'btn btn-default' );
    }

    /** 
     * Set small button
     * @param boolean $_value Set small buttons. Default true.
     */
    public function small( $_value = true )
    {
        $this->mSmall = $_value;
        $this->mClasses[] = 'btn-xs';
        return $this;
    }

    /** 
     * Add a style to button
     * @param string $_style Style/s to add
     */
    public function addStyle( $_style )
    {
        $this->mStyles[] = $_style;
        return $this; 
    }

    /** 
     * Add a class to button
     * @param string $_class Class/es to add, for example "btn-warning"
     */
    public function addClass( $_class )
    {
        $this->mClasses[] = $_class;
        return $this; 
    }

    /** 
     * Add attributes to button
     * @param string $_attributes Attributes to add, for example 'onclick="..."'
     */
    public function addAttribute( $_attributes )
    {
        $this->mAttributes[] = $_attributes;
        return $this; 
    }

    /** 
    * Generate the button
    * @return string dataview
    */
    public function __toString()
    {
        $strTitle = ''; 
        $strLabel = $this->mIcon .' '. $this->mLabel;
        $strPrefix = '';
        $strSufix = '';

        if( $this->mSmall )
        {
            $strTitle = ' title="'. $this->mLabel .'"'; 
            $strLabel = $this->mIcon;
        }

        if( $this->mIsDeleteButton )
        {
            $strPrefix = Form::open(array('method'=>'delete', 
                                    'url' => $this->mDataUri, 
                                    //'data-confirm' => , 
                                    'style'=>'display:inline!important;'));
            $strSufix = Form::close();
            $this->mAttributes[] = 'onclick="if(confirm(\''.trans('forms.confirm_delete').'\')) {'
                                   . 'parentNode.submit(); return true;'
                                   . '} else { return false; }"';
        }

        return $strPrefix
               .'<a role="button" '
               .   'href="'. $this->mDataUri .'" '
               .   'class="'. implode(' ', $this->mClasses) .'" '
               .   'style="'. implode(' ', $this->mStyles) .'" '
               .   implode(' ', $this->mAttributes)
               .   $strTitle
               .'>'
               .   $strLabel
               .'</a>'
               .$strSufix;
    }

    /*

        if( $this->mIsDeleteButton )
        {
            return Form::open( array('url' => $this->mDataUri, 
                                     //'route' => array( $action .'.destroy', $item->id), 
                                     'method' => 'delete', 
                                     'data-confirm' => trans('forms.confirm_delete') ) )

                    .   '<button type="submit" href="'. $this->mDataUri 
                    .       '" class="'. implode(' ', $this->mClasses) .'" title="Eliminar">'
                    .       $strLabel
                    .   '</button>'
                    . Form::close();


            $strPrefix = Form::open(array('method'=>'delete', 
                                    'url' => $this->mDataUri, 
                                    //'data-confirm' => , 
                                    'style'=>'display:inline!important;'));
            $strSufix = Form::close();
            $this->mAttributes[] = 'onclick="if(confirm(\''.trans('forms.confirm_delete').'\')) {'
                                   . 'parentNode.submit(); } return false;"';
        }
    */
}
