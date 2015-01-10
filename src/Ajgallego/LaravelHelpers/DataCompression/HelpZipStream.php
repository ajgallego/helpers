<?php namespace Ajgallego\LaravelHelpers\DataCompression;

/** 
* Wraper class of ZipFile that uses a stream to store contents
* https://github.com/phpmyadmin/phpmyadmin/blob/master/libraries/zip.lib.php
*/
class HelpZipStream
{
	private $mZip;

	/**
	* Private constructor
	*/
	private function __construct( \ZipFile $objZip )
	{
		$this->mZip = $objZip;
	}

	/**
	* Create a new Zip File
	*/
	public static function create()
	{
        $objZip = new \ZipFile();

        if( ! $objZip )
            throw new Exception( 'Error creating stream zip file.' );

		return new HelpZipStream( $objZip );
	}

	/**
	* Add a file to the zip
	* @param string $filename Fullpath to the file to add.
	* @param string $newname Optional parameter used to rename the file.
	*/
	public function addFile( $filename, $newname=null )
	{
		if( file_exists($filename) )
		{
            $contents = file_get_contents( $filename );

            $newname = ( $newname==null ? $filename : $newname );

			$this->mZip->addFile( $contents, $newname );  
		}
		else
            throw new Exception( 'Error when compressing files. File not found: ' . $filename );
	}

	/**
	* Add content to the zip from a stream
	* @param string $filename Name of the file to add.
	* @param string $contents Contents of the new file.
	*/
	public function addFileWithContent( $filename, $contents )
	{
		$this->mZip->addFile( $contents, $filename );
	}

    /**
    * Get the content of the zip.
    * @return Returns the content stream
    */
    public function getContent()
    {
        return $this->mZip->file();
    }

    /**
    * Opens a download dialog in the browser allowing to download the current zip file.
    * @param string $filename Name of the file to download.
    */
    public function download( $filename )
    {
        header('Content-type: application/zip'); //octet-stream');
        header('Content-Disposition: attachment;filename="'. $filename .'"');
        header('Cache-Control: max-age=0'); 
        header('Content-Transfer-Encoding: binary');
        
        echo $this->mZip->file(); 

        return;
    }

	/**
	* Save the current stream zip file to disk.
	* @param string $filename Name (fullpath) of the file to store.
	*/
	public function save( $filename )
	{
		return file_put_contents( $filename, $this->mZip->file() );
	}
}
