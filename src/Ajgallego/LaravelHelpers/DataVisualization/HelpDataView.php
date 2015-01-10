<?php namespace Ajgallego\LaravelHelpers\DataVisualization;

use Illuminate\View\Compilers\BladeCompiler;
use Illuminate\Support\Facades\URL;
use Ajgallego\LaravelHelpers\HtmlGeneration;

/**
* Creates a table to display data. 
* Usually used on the show action of a resource.
*/
class HelpDataView
{
	private $mFields       = array();
    private $mGroupClasses = array();
	private $mButtons      = '';

	/**
	* Private dataview constructor
	*/
	private function __construct() 
	{
		return $this;
	}

	/**
	* Create a dataview from an Uri and a resource_id
	*/
	public static function create()
	{
		return new HelpDataView();
	}

    /**
     * Add a class/es to the dataView group
     * @param string $class Class to add
     */
    public function addGroupClass( $_class )
    {
        $this->mGroupClasses[] = $_class;
        return $this; 
    }

	/**
	 * Add a value to the dataview
     * @param string $label
     * @param string $value
     * @param string $value_style Optional parameters
     */
    public function add($_label, $_value, $_value_style='')
    {
        if( $_value_style != '' )
            $_value_style = ' style="'. $_value_style .'"';

        $this->mFields[] = '<tr><th>'. $_label .'</th><td'. $_value_style .'>'. $_value .'</td></tr>';
        return $this;
    }

	/** 
	 * Add a boolean value to the dataview
     * @param boolean $value
     * @param string $label
     * @param string $value_style Optional parameters
     */
    public function addBool($_label, $_value, $_value_style='')
    {
        if( $_value_style != '' )
            $_value_style = ' style="'. $_value_style .'"';

    	$boolValue = $_value ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>';

    	$this->mFields[] = '<tr><th>'. $_label .'</th><td'. $_value_style .'>'. $boolValue .'</td></tr>';
    	return $this;
    }

    /**
	 * Add a value to the dataview using blade syntax
     * @param string $label
     * @param string $value
     * @param string $params Optional parameters
     */
    public function addBlade($_label, $_value, $_params=array())
    {
    	// blade syntax?
        if (strpos($_value, '{{') !== false || strpos($_value, '@') !== false) 
        {
        	$_value = $this->prv_compileBladeSyntax($_value, $_params);
		}

        $this->mFields[] = '<tr><th>'. $_label .'</th><td>'. $_value .'</td></tr>';
        return $this;
    }

    /** 
	 * Add a link to the dataview
     * @param string $label
     * @param string $value 
     * @param string $url
     */
    public function addLink($_label, $_value, $_url)
    {
    	$this->mFields[] = '<tr><th>'. $_label .'</th><td><a href="'. $_url .'">'. $_value .'</a></td></tr>';
    	return $this;
    }

    /** 
	 * Add a predefined button to modify the data
     * @param string $_uri Button uri
     * @param string $addClass Optionals parameter
     */
    public function addButtonModify( $_uri, $_addClass='' )
    {
		$this->mButtons .= \HelpActionButton::edit( $_uri )
                           ->addStyle('margin-right:10px')
                           ->addClass($_addClass);
		return $this;
	}

	/** 
	 * Add a predefined button to delete the data
     * @param string $_uri Button uri
     * @param string $addClass Optionals parameter
     */
    public function addButtonDelete( $_uri, $_addClass='' )
    {
        $this->mButtons .= \HelpActionButton::delete( $_uri )
                           ->addStyle('margin-right:10px')
                           ->addClass($_addClass);
		return $this;
	}

	/** 
	 * Add a predefined button to return to the list of data
     * @param string $_uri Button uri
     * @param string $addClass Optionals parameter
     */
    public function addButtonReturn( $_uri, $_addClass='' )
    {
		$this->mButtons .= \HelpActionButton::back( $_uri )
                           ->addStyle('margin-right:10px')
                           ->addClass($_addClass);
		return $this;
    }

    /** 
	 * Add predefined buttons to the dataview
     * @param string $icon
     * @param string $label
     * @param string $uri The button uri
     * @param string $addClass Optionals parameter
     */
    public function addButtonCustom( $_icon, $_label, $_uri, $_addClass='' )
    {
		$this->mButtons .= \HelpActionButton::custom( $_icon, $_label, $_uri )
                           ->addStyle('margin-right:10px')
                           ->addClass($_addClass);
		return $this;
    }

	/** 
	* Generate the dataview
	* @return string dataview
	*/
	public function getView()
	{
		$str = '<table class="table table-bordered table-detail '. implode(' ', $this->mGroupClasses) .'">'; 

		foreach( $this->mFields as $key => $fieldValue )
		{
			$str .= $fieldValue;
		}

		$str .= '</table>';

		if( $this->mButtons != '' )
		{
			$str .= '<div class="form-actions" style="vertical-align:middle">'
				    . 	$this->mButtons
				    .'</div>';
		}

		return $str;
	}

    /** 
    * Generate the dataview
    * @return string dataview
    */
    public function __toString()
    {
        return $this->getView();
    }

    /**
     * Compile blade template with passing arguments.
     *
     * @param  string $value
     * @param  array  $args  variables to be extracted
     * @return string        the compiled output
     */
	private function prv_compileBladeSyntax( $value, array $args = array() )
    {
    	$compiler = new BladeCompiler( App::make('files'), App::make('path').'/storage/views' );
        $generated = $compiler->compileString($value);

//return $generated;
        //if( is_array( $args ) == false ) $args = array( $args );

        ob_start() and extract($args, EXTR_SKIP);


eval('?>'.$generated.'<?php ');

        /*try
        {
            eval('?>'.$generated.'<?php ');
        }
        // If we caught an exception, just return $value unparsed for now, or empty string
        catch (\Exception $e)
        {
            ob_get_clean(); //throw $e;
            return $value;
        }
*/
        $content = ob_get_clean();

        return $content;
    }
}