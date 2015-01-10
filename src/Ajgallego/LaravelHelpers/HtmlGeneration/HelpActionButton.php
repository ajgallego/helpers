<?php namespace Ajgallego\LaravelHelpers\HtmlGeneration;

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

    private $mConfirmation   = false;
    private $mConfirmationMessage = ''; 


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
    */
    public static function show( $_data_uri )
    {
        return new HelpActionButton( '<i class="fa fa-fw fa-eye"></i>', 
                                     trans('forms.actions.show'), 
                                     $_data_uri, 
                                     'btn btn-success' );
    }

    /**
    * Create a new button
    * @param string $data_uri Used to generate the edit, delete and return buttons
    */
    public static function create( $_data_uri )
    {
        return new HelpActionButton( '<i class="fa fa-fw fa-plus-circle"></i>',
                                     trans('forms.actions.create'), 
                                     $_data_uri, 
                                     'btn btn-success' );
    }

    /**
    * Create an edit button
    * @param string $data_uri Used to generate the edit, delete and return buttons
    */
    public static function edit( $_data_uri )
    {
        return new HelpActionButton( '<i class="fa fa-fw fa-pencil"></i>', 
                                     trans('forms.actions.edit'), 
                                     $_data_uri, 
                                     'btn btn-success' );
    }

    /**
    * Create a delete button
    * @param string $data_uri Used to generate the edit, delete and return buttons
    */
    public static function delete( $_data_uri )
    {
        return (new HelpActionButton( '<i class="fa fa-fw fa-trash-o"></i>', 
                                     trans('forms.actions.delete'), 
                                     $_data_uri, 
                                     'btn btn-danger', 
                                     true ));
    }

    /**
    * Create a return button
    * @param string $data_uri Used to generate the edit, delete and return buttons
    */
    public static function back( $_data_uri )
    {
        return new HelpActionButton( '<i class="fa fa-fw fa-chevron-left"></i>', 
                                     trans('forms.actions.return'), 
                                     $_data_uri, 
                                     'btn btn-default' );
    }

    /**
    * Create a custom button
    * @param string $_icon The icon to show
    * @param integer $_label The label of the button
    * @param string $_uri The action uri of the button
    */
    public static function custom( $_icon, $_label, $_uri, $_classes = 'btn btn-default' )
    {
        return new HelpActionButton( $_icon, 
                                     $_label, 
                                     $_uri, 
                                     $_classes );
    }

    /** 
     * Set small button (by default buttons have regular size)
     */
    public function small()
    {
        $this->mSmall = true;
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
     * Enable confirmation dialog before button action (by default it is disabled)
     * @param string $_message Optional parameter, allows to change the confirmation message
     */
    public function enableConfirmation( $_message = '' )
    {
        $this->mConfirmation = true;
        $this->mConfirmationMessage = ( $_message == '' ? trans('forms.confirm_default') : $_message );
        return $this; 
    }

    /** 
     * Disable confirmation dialog (by default it is disabled)
     */
    public function disableConfirmation()
    {
        $this->mConfirmation = false;
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
                                    'style'=>'display:inline!important;padding:0px;margin:0px'));
            $strSufix = Form::close();

            $this->enableConfirmation( trans('forms.confirm.text_delete') );
        }

        if( $this->mConfirmation )
        {
            $dialogTitle = trans('forms.confirm.title');
            $dialogMessage = $this->mConfirmationMessage;
            $dialogButtonOk = trans('forms.confirm.button_confirm');
            $dialogButtonCancel = trans('forms.confirm.button_cancel');
            $dialogAction = ( $this->mIsDeleteButton ? 'parentNode.submit()' : 'window.location.href=\'$this->mDataUri\'' );
            $dialog = <<<EOD
                BootstrapDialog.show({ 
                    title:'$dialogTitle', message:'$dialogMessage', type:BootstrapDialog.TYPE_DEFAULT, 
                    buttons: [                        
                        {label:'$dialogButtonCancel', action:function(dialog){dialog.close();}}, 
                        {label:'$dialogButtonOk', cssClass:'btn-primary', action:function(dialog){ $dialogAction; dialog.close();}}, 
                    ]
                });
EOD;
            $this->mAttributes[] = 'onclick="'. trim( $dialog ) .'"';
            $this->mDataUri = '#';
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
}
