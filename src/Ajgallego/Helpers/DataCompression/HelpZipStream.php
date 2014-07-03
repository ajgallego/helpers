<?php 

namespace Ajgallego\Helpers\DataCompression;

// https://github.com/phpmyadmin/phpmyadmin/blob/master/libraries/zip.lib.php

class HelpZipStream
{
	private $mZip;

	//-------------------------------------------------------------------------
	private function __construct( \ZipFile $objZip )
	{
		$this->mZip = $objZip;
	}

	//-------------------------------------------------------------------------
	public static function create()
	{
        $objZip = new \ZipFile();

        if( ! $objZip )
            throw new Exception( 'Error creating stream zip file.' );

		return new HelpZipStream( $objZip );
	}

	//-------------------------------------------------------------------------
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

	//-------------------------------------------------------------------------
	public function addFileWithContent( $filename, $contents )
	{
		$this->mZip->addFile( $contents, $filename );
	}

    //-------------------------------------------------------------------------
    public function getContent()
    {
        return $this->mZip->file();
    }

    //-------------------------------------------------------------------------
    public function download( $filename )
    {
        header('Content-type: application/zip'); //octet-stream');
        header('Content-Disposition: attachment;filename="'. $filename .'"');
        header('Cache-Control: max-age=0'); 
        header('Content-Transfer-Encoding: binary');
        
        echo $this->mZip->file(); 

        return;
    }

	//-------------------------------------------------------------------------
	public function save( $filename )
	{
		return file_put_contents( $filename, $this->mZip->file() );
	}
}
