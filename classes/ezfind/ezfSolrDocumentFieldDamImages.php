<?php
/*
 * Indexing logic for the datatype dam_images - ezfind uses it
 * 
 */

class ezfSolrDocumentFieldDamImages extends ezfSolrDocumentFieldBase
{
	/**
	 * (non-PHPdoc)
	 * @see ezfSolrDocumentFieldBase::getData()
	 */	
	public function getData()
	{
		$return = array();
		
		$values = unserialize( $this->ContentObjectAttribute->attribute( 'data_text' ) );
		
		if( !empty( $values ) )
		{
			foreach( $values as $ratio_identifier => $link )
			{
				$return[ 'attr_dam_images_'. $ratio_identifier .'____s' ] = $link;
			}
		}
		
		return $return;
	}
}
