<?php namespace Ajgallego\Laravel-Helpers\DataCompression;

/** 
* Wraper class of ZipArchive
* http://www.php.net/manual/en/class.ziparchive.php
*/
class HelpZip
{
	private $mZip;
    private $mIsClosed;

	/**
    * Private constructor
    * @param ZipArchive $objZip Creates an instance of this class
    */
	private function __construct( ZipArchive $objZip )
	{
		$this->mZip = $objZip;
        $this->mIsClosed = false;
	}

    /**
    * Destructor overloading. It ensures files are closed.
    */
	function __destruct() 
	{
		if( $this->mIsClosed == false ) 
            $this->mZip->close();
	}

	/**
    * Create a new zip file.
    * @param string $filename Zip filename
    */
	public static function create( $filename )
	{
		$objZip = new ZipArchive();
		$objZip->open( $filename, ZipArchive::CREATE );
        
        if( ! $objZip )
            throw new Exception( 'Error creating zip file '. $filename );

		return new HelpZip( $objZip );
	}
    
    /**
    * Open a zip file
    * @param string $filename Name of the file to open (including the path)
    */
	public static function open( $filename )
	{
        if( file_exists($filename) == false )
            throw new Exception( 'Error: File not found: '. $filename );

        $objZip = new ZipArchive();
        $objZip->open( $filename ); 

        if( ! $objZip )
            throw new Exception( 'Error opening zip file: '. $filename );

        return new HelpZip( $objZip );
    }
    
    /**
    * Get the number of files in the zip.
    * @return integer Number of files.
    */
    public function getNumFiles()
    {
        return $this->mZip->numFiles;
    }

    /**
    * Return an array of information of the current zip file.
    * @return array Array of information
    */
    public function getInfo()
    {
        $info = array();
        $info['status'] = $this->mZip->status;
        $info['statusSys'] = $this->mZip->statusSys;
        $info['numFiles'] = $this->mZip->numFiles;
        $info['filename'] = $this->mZip->filename;
        $info['comment'] = $this->mZip->comment;
        $info['files'] = array();

        for( $i = 0; $i < $this->mZip->numFiles; $i++ )
        {
            $info['files'][$i] = $this->mZip->statIndex( $i );
        }

        return $info;
    }

	/**
    * Add the content of a file to the zip
    * @param string $filename Fullpath to the file to add
    * @param string $newname Optional parameter. Use only to rename the file.
    */
	public function addFile( $filename, $newname=null )
	{
		if( file_exists($filename) )
		{
			$this->mZip->addFile( $filename, $newname );  
		}
		else
            throw new Exception( 'Error when compressing files. File not found: ' . $filename );
	}

	/**
    * Add a new file to the zip from string content.
    * @param string $filename Name of new file to create.
    * @param string $contents Content of the file to add.
    */
	public function addFileWithContent( $filename, $contents )
	{
		$this->mZip->addFromString( $filename, $contents );
	}

    /**
    * Get the contents of an entry
    * @param string $filename Fullpath to the file 
    * @return Returns the contents of the entry on success or FALSE on failure. 
    */
	public function getContentFromName( $filename )
	{
        return $this->mZip->getFromName($filename);
    }

    /**
    * Get the contents of an entry using its index.
    * @param string $index Index of the file to open.
    * @return Entry contents or FALSE on failure. 
    */
	public function getContentFromIndex( $index )
	{
        // www.php.net/manual/en/ziparchive.getfromindex.php
        return $this->mZip->getFromIndex($index);
    }

    /**
    * Get the name of an entry using its index
    * @param string $index Index of the entry.
    * @return Name of the entry or FALSE on failure. 
    */
	public function getNameFromIndex( $index )
	{
        return $this->mZip->getNameIndex($index);
    }

    /**
    * Extract a file to disk.
    * @param string/array $fileToExtract Files to extract. Accepts either a single name or an array of names.
    * @param string $destinationPath Path where the extracted files are placed.
    * @param string $newBasename Optional parameter used to rename the file
    * @return Returns true on success and false on failure.
    */
	public function extractOneFile( $fileToExtract, $destinationPath, $newBasename = null )
	{
        $zipname = $this->mZip->filename;

        if( $newBasename != null ) {
            $extension = pathinfo( $fileToExtract, PATHINFO_EXTENSION );
            $destinationFilename = $destinationPath . $newBasename .'.'. $extension;
        }
        else
            $destinationFilename = $destinationPath . $fileToExtract;

        copy( "zip://". $zipname ."#". $fileToExtract, $destinationFilename );

        return $destinationFilename;
    }

	/**
    * Save the contents of the current (new or modificated) zip file.
    * It also close the file.
    */
	public function save()
	{
        $this->mIsClosed = true;
		return $this->mZip->close();
	}
}
