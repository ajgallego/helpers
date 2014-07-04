<?php 

namespace Ajgallego\Helpers\DataVisualization;

use Illuminate\View\Compilers\BladeCompiler;
use Ajgallego\Helpers\HtmlGeneration;

/**
* Creates a table to display data. 
* Usually used on the show action of a resource.
*/
class HelpDataView
{
	private $mFields = array();
	private $mButtons = '';
	private $mDataUri = '';
	private $mDataId = '';

	/**
	* Private dataview constructor
	* @param string $data_uri The uri to your resource, it is used to generate the button actions routes
	*/
	private function __construct( $_data_uri, $_resource_id ) 
	{
		$this->mDataUri = $_data_uri;
		$this->mDataId = $_resource_id;
		return $this;
	}

	/**
	* Create a dataview from an Uri and a resource_id
    * @param string $data_uri Used to generate the edit, delete and return buttons
    * @param integer $resource_id Used to generate the edit and delete buttons
	*/
	public static function create( $_data_uri, $_resource_id )
	{
		return new HelpDataView( $_data_uri, $_resource_id );
	}

	/**
	 * Add a value to the dataview
     * @param string $label
     * @param string $value
     */
    public function add($_label, $_value)
    {
        $this->mFields[] = '<tr><th>'. $_label .'</th><td>'. $_value .'</td></tr>';
        return $this;
    }

	/** 
	 * Add a boolean value to the dataview
     * @param boolean $value
     * @param string $label
     */
    public function addBool($_label, $_value)
    {
    	$boolValue = $_value ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>';
    	$this->mFields[] = '<tr><th>'. $_label .'</th><td>'. $boolValue .'</td></tr>';
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
	 * Add a boolean value to the dataview
     * @param boolean $value
     * @param string $label
     */
    public function addLink($_label, $_value, $_url)
    {
    	$this->mFields[] = '<tr><th>'. $_label .'</th><td><a href="'. $_url .'">'. $_value .'</a></td></tr>';
    	return $this;
    }

    /** 
	 * Add a predefined button to modify the data
     * @param string $addClass Optionals parameter
     */
    public function addButtonModify( $_addClass='' )
    {
		$this->mButtons .= \HelpActionButton::edit($this->mDataUri, $this->mDataId)
                           ->addStyle('margin-right:10px')
                           ->addClass($_addClass);
		return $this;
	}

	/** 
	 * Add a predefined button to delete the data
     * @param string $addClass Optionals parameter
     */
    public function addButtonDelete( $_addClass='' )
    {
        $this->mButtons .= \HelpActionButton::delete($this->mDataUri, $this->mDataId)
                           ->addStyle('margin-right:10px')
                           ->addClass($_addClass);
		return $this;
	}

	/** 
	 * Add a predefined button to return to the list of data
     * @param string $addClass Optionals parameter
     */
    public function addButtonReturn( $_addClass='' )
    {
		$this->mButtons .= \HelpActionButton::back($this->mDataUri)
                           ->addStyle('margin-right:10px')
                           ->addClass($_addClass);
		return $this;
    }

    /** 
	 * Add predefined buttons to the dataview
     * @param string $uri The 
     * @param string $label
     */
    public function addButtonCustom( $_icon, $_label, $_uri, $_addClass='' )
    {
		$this->mButtons .= \HelpActionButton::custom($_icon, $_label, $_uri)
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
		$str = '<table class="table table-bordered table-detail">'; 

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